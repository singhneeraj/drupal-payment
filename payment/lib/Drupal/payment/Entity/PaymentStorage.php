<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentStorage.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\payment\Payment as PaymentServiceWrapper;

/**
 * Handles storage for payment entities.
 */
class PaymentStorage extends ContentEntityDatabaseStorage implements PaymentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function create(array $values = array()) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = parent::create($values);
    $status = PaymentServiceWrapper::statusManager()->createInstance('payment_created')
      ->setCreated(time());
    $payment->setStatus($status);

    return $payment;
  }

  /**
   * {@inheritdoc}
   */
  function mapFromStorageRecords(array $records) {
    foreach ($records as $id => $record) {
      $payment_method = $record->payment_method_id ? PaymentServiceWrapper::methodManager()->createInstance($record->payment_method_id, unserialize($record->payment_method_configuration)) : NULL;
      $payment_type = PaymentServiceWrapper::typeManager()->createInstance($record->payment_type_id, unserialize($record->payment_type_configuration));
      $records[$id] = (object) array(
        'currency' => $record->currency_code,
        'id' => (int) $record->id,
        'owner' => (int) $record->owner_id,
        'method' => $payment_method,
        'type' => $payment_type,
        'bundle' => $payment_type->getPluginId(),
        'uuid' => $record->uuid,
      );
    }

    return parent::mapFromStorageRecords($records);
  }

  /**
   * {@inheritdoc}
   */
  protected function mapToStorageRecord(EntityInterface $entity, $table_key = 'data_table') {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $entity;

    $record = new \stdClass();
    $record->bundle = $payment->bundle();
    $record->currency_code = $payment->getCurrencyCode();
    $record->id = $payment->id();
    $record->first_payment_status_id = current($payment->getStatuses())->getId();
    $record->last_payment_status_id = $payment->getStatus()->getId();
    $record->owner_id = $payment->getOwnerId();
    $record->payment_method_configuration = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getConfiguration() : array();
    $record->payment_method_id = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginId() : NULL;
    $record->payment_type_configuration = $payment->getPaymentType()->getConfiguration();
    $record->payment_type_id = $payment->getPaymentType()->getPluginId();
    $record->uuid = $payment->uuid();

    return $record;
  }

  /**
   * {@inheritdoc}
   */
  public function loadLineItems(array $entities) {
    /** @var \Drupal\payment\Entity\PaymentInterface[] $entities */
    $manager = PaymentServiceWrapper::lineItemManager();
    $result = db_select('payment_line_item', 'pli')
      ->fields('pli', array('plugin_configuration', 'plugin_id'))
      ->condition('payment_id', array_keys($entities))
      ->execute();
    while ($line_item_data = $result->fetchAssoc()) {
      /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
      $line_item = $manager->createInstance($line_item_data['plugin_id'], unserialize($line_item_data['plugin_configuration']));
      $entities[$line_item->getPaymentId()]->setLineItem($line_item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveLineItems(array $line_items) {
    $this->deleteLineItems(array_keys($line_items));
    $query = db_insert('payment_line_item')
      ->fields(array('amount', 'amount_total', 'currency_code', 'name', 'payment_id', 'plugin_configuration', 'plugin_id', 'quantity'));
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[] $entity_line_items */
    foreach ($line_items as $payment_id => $entity_line_items) {
      foreach ($entity_line_items as $line_item) {
        $line_item->setPaymentId($payment_id);
        $query->values(array(
          'amount' => $line_item->getAmount(),
          'amount_total' => $line_item->getTotalAmount(),
          'currency_code' => $line_item->getCurrencyCode(),
          'name' => $line_item->getName(),
          'payment_id' => $line_item->getPaymentId(),
          'plugin_configuration' => serialize($line_item->getConfiguration()),
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
  public function loadPaymentStatuses(array $entities) {
    /** @var \Drupal\payment\Entity\PaymentInterface[] $entities */
    $manager = PaymentServiceWrapper::statusManager();
    $result = db_select('payment_status', 'ps')
      ->fields('ps')
      ->condition('payment_id', array_keys($entities))
      ->orderBy('id', 'ASC')
      ->execute();
    $statuses= array_fill_keys(array_keys($entities), array());
    while ($status_data = $result->fetchAssoc()) {
      $plugin_id = $status_data['plugin_id'];
      /** @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface $status */
      $status = $manager->createInstance($plugin_id, array(
        'created' => (int) $status_data['created'],
        'paymentId' => (int) $status_data['payment_id'],
      ));
      $statuses[$status->getPaymentId()][] = $status;
    }
    foreach ($entities as $payment) {
      $payment->setStatuses($statuses[$payment->id()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function savePaymentStatuses(array $statuses) {
    /** @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface[] $entity_statuses */
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
      db_update('payment')
        ->condition('id', $payment_id)
        ->fields(array(
          'first_payment_status_id' => reset($entity_statuses)->getId(),
          'last_payment_status_id' => end($entity_statuses)->getId(),
        ))
        ->execute();
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