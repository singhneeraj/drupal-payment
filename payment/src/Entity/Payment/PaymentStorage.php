<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentStorage.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityDatabaseStorage;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles storage for payment entities.
 */
class PaymentStorage extends ContentEntityDatabaseStorage implements PaymentStorageInterface {

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   */
  protected $paymentLineItemManager;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The payment type manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface
   */
  protected $paymentTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface $payment_line_item_manager
   *   The payment line item manager.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   *   The payment method manager.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status manager.
   * @param \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface $payment_type_manager
   *   The payment type manager.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, PaymentLineItemManagerInterface $payment_line_item_manager, PaymentMethodManagerInterface $payment_method_manager, PaymentStatusManagerInterface $payment_status_manager, PaymentTypeManagerInterface $payment_type_manager) {
    parent::__construct($entity_type, $database, $entity_manager);
    $this->paymentLineItemManager = $payment_line_item_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->paymentStatusManager = $payment_status_manager;
    $this->paymentTypeManager = $payment_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container->get('database'), $container->get('entity.manager'), $container->get('plugin.manager.payment.line_item'), $container->get('plugin.manager.payment.method'), $container->get('plugin.manager.payment.status'), $container->get('plugin.manager.payment.type'));
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = array()) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = parent::create($values);
    $status = $this->paymentStatusManager->createInstance('payment_created')
      ->setCreated(time());
    $payment->setStatus($status);

    return $payment;
  }

  /**
   * {@inheritdoc}
   */
  function mapFromStorageRecords(array $records) {
    foreach ($records as $id => $record) {
      $payment_method = $record->payment_method_id ? $this->paymentMethodManager->createInstance($record->payment_method_id, unserialize($record->payment_method_configuration)) : NULL;
      $payment_type = $this->paymentTypeManager->createInstance($record->payment_type_id, unserialize($record->payment_type_configuration));
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
  protected function mapToStorageRecord(ContentEntityInterface $entity, $table_key = 'base_table') {
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
    $result = $this->database->select('payment_line_item', 'pli')
      ->fields('pli', array('plugin_configuration', 'plugin_id'))
      ->condition('payment_id', array_keys($entities))
      ->execute();
    while ($line_item_data = $result->fetchAssoc()) {
      /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
      $line_item = $this->paymentLineItemManager->createInstance($line_item_data['plugin_id'], unserialize($line_item_data['plugin_configuration']));
      $entities[$line_item->getPaymentId()]->setLineItem($line_item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveLineItems(array $line_items) {
    $this->deleteLineItems(array_keys($line_items));
    $query = $this->database->insert('payment_line_item')
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
    $this->database->delete('payment_line_item')
      ->condition('payment_id', $ids)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function loadPaymentStatuses(array $entities) {
    /** @var \Drupal\payment\Entity\PaymentInterface[] $entities */
    $result = $this->database->select('payment_status', 'ps')
      ->fields('ps')
      ->condition('payment_id', array_keys($entities))
      ->orderBy('id', 'ASC')
      ->execute();
    $statuses= array_fill_keys(array_keys($entities), array());
    while ($status_data = $result->fetchAssoc()) {
      $plugin_id = $status_data['plugin_id'];
      /** @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface $status */
      $status = $this->paymentStatusManager->createInstance($plugin_id, array(
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
      $this->database->update('payment')
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
    $this->database->delete('payment_status')
      ->condition('payment_id', $ids)
      ->execute();
  }
}
