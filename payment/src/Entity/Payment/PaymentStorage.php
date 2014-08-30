<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentStorage.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Cache\CacheBackendInterface;
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
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The entity cache.
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface $payment_line_item_manager
   *   The payment line item manager.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   *   The payment method manager.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status manager.
   * @param \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface $payment_type_manager
   *   The payment type manager.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, CacheBackendInterface $cache, PaymentLineItemManagerInterface $payment_line_item_manager, PaymentMethodManagerInterface $payment_method_manager, PaymentStatusManagerInterface $payment_status_manager, PaymentTypeManagerInterface $payment_type_manager) {
    parent::__construct($entity_type, $database, $entity_manager, $cache);
    $this->paymentLineItemManager = $payment_line_item_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->paymentStatusManager = $payment_status_manager;
    $this->paymentTypeManager = $payment_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container->get('database'), $container->get('entity.manager'), $container->get('cache.entity'), $container->get('plugin.manager.payment.line_item'), $container->get('plugin.manager.payment.method'), $container->get('plugin.manager.payment.status'), $container->get('plugin.manager.payment.type'));
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = array()) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = parent::create($values);
    $status = $this->paymentStatusManager->createInstance('payment_created')
      ->setCreated(time());
    $payment->setPaymentStatus($status);

    return $payment;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = $this->database->select('payment', 'payment');
    $query->addTag('payment_load_multiple');
    $query->fields('payment');
    if ($ids) {
      $query->condition('id', $ids);
    }

    return $query;
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
    $record->first_payment_status_id = current($payment->getPaymentStatuses())->getId();
    $record->last_payment_status_id = $payment->getPaymentStatus()->getId();
    $record->owner_id = $payment->getOwnerId();
    $record->payment_method_configuration = serialize($payment->getPaymentMethod() ? $payment->getPaymentMethod()->getConfiguration() : array());
    $record->payment_method_id = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginId() : NULL;
    $record->payment_type_configuration = serialize($payment->getPaymentType()->getConfiguration());
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
      ->fields('pli', array('payment_id', 'plugin_configuration', 'plugin_id'))
      ->condition('payment_id', array_keys($entities))
      ->execute();
    while ($line_item_data = $result->fetchAssoc()) {
      /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
      $line_item = $this->paymentLineItemManager->createInstance($line_item_data['plugin_id'], unserialize($line_item_data['plugin_configuration']));
      $line_item->setPayment($entities[$line_item_data['payment_id']]);
      $entities[$line_item_data['payment_id']]->setLineItem($line_item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveLineItems(array $line_items) {
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[] $line_items */
    if ($line_items) {
      $this->deleteLineItems(array(reset($line_items)->getPayment()->id()));
      $query = $this->database->insert('payment_line_item')
        ->fields(array('amount', 'amount_total', 'currency_code', 'name', 'payment_id', 'plugin_configuration', 'plugin_id', 'quantity'));
      foreach ($line_items as $line_item) {
        $query->values(array(
          'amount' => $line_item->getAmount(),
          'amount_total' => $line_item->getTotalAmount(),
          'currency_code' => $line_item->getCurrencyCode(),
          'name' => $line_item->getName(),
          'payment_id' => $line_item->getPayment()->id(),
          'plugin_configuration' => serialize($line_item->getConfiguration()),
          'plugin_id' => $line_item->getPluginId(),
          'quantity' => $line_item->getQuantity(),
        ));
      }
      $query->execute();
    }
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
      $status = $this->paymentStatusManager->createInstance($status_data['plugin_id']);
      $status->setCreated((int) $status_data['created']);
      $status->setId((int) $status_data['id']);
      $status->setPayment($entities[$status_data['payment_id']]);

      $statuses[$status->getPayment()->id()][] = $status;
    }
    foreach ($entities as $payment) {
      $payment->setPaymentStatuses($statuses[$payment->id()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function savePaymentStatuses(array $statuses) {
    /** @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface[] $statuses */
    if ($statuses) {
      foreach ($statuses as $status) {
        // Statuses cannot be edited, so only save the ones without an ID.
        if (!$status->getId()) {
          $record = array(
            'created' => $status->getCreated(),
            'payment_id' => $status->getPayment()->id(),
            'plugin_id' => $status->getPluginId(),
          );
          drupal_write_record('payment_status', $record);
          $status->setId($record['id']);
        }
      }
      debug(reset($statuses)->getPayment()->id());
      $this->database->update('payment')
        ->condition('id', reset($statuses)->getPayment()->id())
        ->fields(array(
          'first_payment_status_id' => reset($statuses)->getId(),
          'last_payment_status_id' => end($statuses)->getId(),
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

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema['payment'] = array(
      'fields' => array(
        'currency_code' => array(
          'type' => 'varchar',
          'length' => 3,
          'default' => 'XXX',
          'not null' => TRUE,
        ),
        'bundle' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
        'id' => array(
          'type' => 'serial',
        ),
        'first_payment_status_id' => array(
          'description' => "The {payment_status_item}.id of this payment's first status item.",
          'type' => 'int',
          'unsigned' => TRUE,
          'default' => 0,
          'not null' => TRUE,
        ),
        'last_payment_status_id' => array(
          'description' => "The {payment_status_item}.id of this payment's most recent status item.",
          'type' => 'int',
          'unsigned' => TRUE,
          'default' => 0,
          'not null' => TRUE,
        ),
        'owner_id' => array(
          'description' => 'The {users}.uid this payment belongs to.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'payment_method_configuration' => array(
          'type' => 'blob',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'payment_method_id' => array(
          'type' => 'varchar',
          'length' => 255,
        ),
        'payment_type_configuration' => array(
          'type' => 'blob',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'payment_type_id' => array(
          'type' => 'varchar',
          'length' => 255,
        ),
        'uuid' => array(
          'description' => 'Unique Key: Universally unique identifier for this entity.',
          'type' => 'varchar',
          'length' => 128,
          'not null' => FALSE,
        ),
      ),
      'foreign keys' => array(
        'first_payment_status_id' => array(
          'table' => 'payment_status_item',
          'columns' => array(
            'first_payment_status_id' => 'id',
          ),
        ),
        'last_payment_status_id' => array(
          'table' => 'payment_status_item',
          'columns' => array(
            'last_payment_status_id' => 'id',
          ),
        ),
        'owner_id' => array(
          'table' => 'user',
          'columns' => array(
            'owner_id' => 'uid',
          ),
        ),
      ),
      'indexes' => array(
        'id' => array('id'),
        'uuid' => array('uuid'),
      ),
      'primary key' => array('id'),
    );

    return $schema;
  }

}
