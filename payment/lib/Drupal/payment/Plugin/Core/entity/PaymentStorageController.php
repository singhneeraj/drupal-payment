<?php

/**
 * @file
 * Contains Drupal\payment\Plugin\Core\entity\PaymentStorageController.
 */

namespace Drupal\payment\Plugin\Core\entity;

use Drupal\Core\Entity\DatabaseStorageControllerNG;
use Drupal\Core\Entity\EntityInterface;

/**
 * Handles storage for payment entities.
 */
class PaymentStorageController extends DatabaseStorageControllerNG implements PaymentStorageControllerInterface {

  /**
   * {@inheritdoc}
   */
  function create(array $values) {
    $payment = parent::create($values);
    $payment->setStatus(\Drupal::service('plugin.manager.payment.status')->createInstance('payment_created'));

    return $payment;
  }

  /**
   * {@inheritdoc}
   */
  function attachLoad(&$queried_entities, $load_revision = FALSE) {
    $manager = \Drupal::service('plugin.manager.payment.context');
    $line_items = $this->loadLineItems(array_keys($queried_entities));
    $statuses = $this->loadPaymentStatuses(array_keys($queried_entities));
    foreach ($queried_entities as $id => $queried_entity) {
      $queried_entities[$id] = (object) array(
        'context' => $queried_entity->context_plugin_id ? $manager->createInstance($queried_entity->context_plugin_id) : NULL,
        'currencyCode' => $queried_entity->currency_code,
        'id' => (int) $queried_entity->id,
        'lineItems' => $line_items[$id],
        'ownerId' => (int) $queried_entity->owner_id,
        'paymentMethodId' => $queried_entity->payment_method_id,
        'statuses' => $statuses[$id],
        'uuid' => $queried_entity->uuid,
      );
    }
    parent::attachLoad($queried_entities, $load_revision);
  }

  /**
   * {@inheritdoc}
   */
  public function baseFieldDefinitions() {
    $fields = parent::baseFieldDefinitions();
    $fields['currencyCode'] = array(
      'label' => t('Currency code'),
      'settings' => array(
        'default_value' => 'XXX',
      ),
      'type' => 'string_field',
    );
    $fields['id'] = array(
      'label' => t('Payment ID'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $fields['paymentMethodId'] = array(
      'label' => t('Payment method ID'),
      'type' => 'string_field',
    );
    $fields['ownerId'] = array(
      'label' => t('Owner'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $fields['uuid'] = array(
      'label' => t('UUID'),
      'read-only' => TRUE,
      'type' => 'uuid_field',
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapToStorageRecord(EntityInterface $entity) {
    $record = new \stdClass();
    $record->context_plugin_id = $entity->getPaymentContext() ? $entity->getPaymentContext()->getPluginId() : NULL;
    $record->currency_code = $entity->getCurrencyCode();
    $record->id = $entity->id();
    $record->payment_method_id = $entity->getPaymentMethodId();
    $record->first_payment_status_id = current($entity->getStatuses())->getId();
    $record->last_payment_status_id = $entity->getStatus()->getId();
    $record->owner_id = $entity->getOwnerId();
    $record->uuid= $entity->uuid();

    return $record;
  }

  /**
   * {@inheritdoc}
   */
  public function loadLineItems(array $ids) {
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    $result = db_select('payment_line_item', 'pli')
      ->fields('pli')
      ->condition('payment_id', $ids)
      ->execute();
    $line_items = array_fill_keys($ids, array());
    while ($line_item_data = $result->fetchAssoc()) {
      $plugin_id = $line_item_data['plugin_id'];
      $line_item = $manager->createInstance($plugin_id, array(
        'amount' => (float) $line_item_data['amount'],
        'name' => $line_item_data['name'],
        'paymentId' => (int) $line_item_data['payment_id'],
        'quantity' => (int) $line_item_data['quantity'],
      ));
      $line_items[$line_item->getPaymentId()][$line_item->getName()] = $line_item;
    }

    return $line_items;
  }

  /**
   * {@inheritdoc}
   */
  public function saveLineItems(array $line_items) {
    $this->deleteLineItems(array_keys($line_items));
    $query = db_insert('payment_line_item')
      ->fields(array('amount', 'amount_total', 'name', 'payment_id', 'plugin_id', 'quantity'));
    foreach ($line_items as $payment_id => $entity_line_items) {
      foreach ($entity_line_items as $line_item) {
        $line_item->setPaymentId($payment_id);
        $query->values(array(
          'amount' => $line_item->getAmount(),
          'amount_total' => $line_item->getTotalAmount(),
          'name' => $line_item->getName(),
          'payment_id' => $line_item->getPaymentId(),
          'plugin_id' => $line_item->getPluginId(),
          'quantity' => $line_item->getQuantity(),
        ));
      }
    }
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLineItems(array $ids) {
    db_delete('payment_line_item')
      ->condition('payment_id', $ids)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function loadPaymentStatuses(array $ids) {
    $manager = \Drupal::service('plugin.manager.payment.status');
    $result = db_select('payment_status', 'ps')
      ->fields('ps')
      ->condition('payment_id', $ids)
      ->orderBy('id', 'ASC')
      ->execute();
    $statuses= array_fill_keys($ids, array());
    while ($status_data = $result->fetchAssoc()) {
      $plugin_id = $status_data['plugin_id'];
      $status = $manager->createInstance($plugin_id, array(
        'created' => (int) $status_data['created'],
        'paymentId' => (int) $status_data['payment_id'],
      ));
      $statuses[$status->getPaymentId()][] = $status;
    }

    return $statuses;
  }

  /**
   * {@inheritdoc}
   */
  public function savePaymentStatuses(array $statuses) {
    foreach ($statuses as $payment_id => $entity_statuses) {
      foreach ($entity_statuses as $status) {
        // Statuses cannot be edited, so only save the ones without an ID.
        if (!$status->getId()) {
          $status->setPaymentId($payment_id);
          $record = array(
            'created' => $status->getCreated(),
            'payment_id' => $status->getPaymentId(),
            'plugin_id' => $status->getPluginId(),
          );
          drupal_write_record('payment_status', $record);
          $status->setId($record['id']);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaymentStatuses(array $ids) {
    db_delete('payment_status')
      ->condition('payment_id', $ids)
      ->execute();
  }
}
