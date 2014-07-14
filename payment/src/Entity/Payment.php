<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\Payment.
 */

namespace Drupal\payment\Entity;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\Event\PaymentStatusSet;
use Drupal\payment\Payment as PaymentServiceWrapper;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface as PluginPaymentStatusInterface;
use Drupal\user\UserInterface;

/**
 * Defines a payment entity.
 *
 * @ContentEntityType(
 *   base_table = "payment",
 *   bundle_label = @Translation("Payment type"),
 *   controllers = {
 *     "access" = "Drupal\payment\Entity\Payment\PaymentAccess",
 *     "form" = {
 *       "delete" = "Drupal\payment\Entity\Payment\PaymentDeleteForm",
 *       "edit" = "Drupal\payment\Entity\Payment\PaymentEditForm",
 *       "update_status" = "Drupal\payment\Entity\Payment\PaymentStatusForm",
 *       "capture" = "Drupal\payment\Entity\Payment\PaymentCaptureForm",
 *       "refund" = "Drupal\payment\Entity\Payment\PaymentRefundForm"
 *     },
 *     "list_builder" = "Drupal\payment\Entity\Payment\PaymentListBuilder",
 *     "view_builder" = "Drupal\payment\Entity\Payment\PaymentViewBuilder",
 *     "storage" = "Drupal\payment\Entity\Payment\PaymentStorage",
 *   },
 *   entity_keys = {
 *     "bundle" = "bundle",
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   fieldable = TRUE,
 *   id = "payment",
 *   label = @Translation("Payment"),
 *   links = {
 *     "admin-form" = "payment.payment_type",
 *     "canonical" = "payment.payment.view",
 *     "edit-form" = "payment.payment.edit",
 *     "delete-form" = "payment.payment.delete",
 *     "update-status-form" = "payment.payment.update_status",
 *     "capture-form" = "payment.payment.capture",
 *     "refund-form" = "payment.payment.refund"
 *   }
 * )
 */
class Payment extends ContentEntityBase implements PaymentInterface {

  /**
   * The payment method.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  protected $method;

  /**
   * The payment type.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface
   */
  protected $type;

  /**
   * Line items.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[]
   *   Keys are line item machine names.
   */
  protected $lineItems = array();

  /**
   * Payment statuses.
   *
   * @var array
   *   Values are \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface
   *   objects.
   */
  protected $statuses = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type, $bundle = FALSE, $translations = array()) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    $payment_type_manager = PaymentServiceWrapper::typeManager();
    if ($payment_type_manager instanceof CachedDiscoveryInterface) {
      $payment_type_manager->clearCachedDefinitions();
    }
    // When a payment is newly created, its bundle is set, but there is no
    // plugin yet.
    if (!isset($values['type'])) {
      $this->type = PaymentServiceWrapper::typeManager()->createInstance($this->bundle());
    }
    $this->type->setPayment($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->getStatus()->getCreated();
  }

  /**
   * {@inheritdoc}
   */
  public function label($langcode = NULL) {
    return $this->getPaymentType()->paymentDescription($langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrency() {
    return $this->get('currency')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyCode($currency_code) {
    $this->set('currency', $currency_code);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyCode() {
    return $this->get('currency')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setLineItems(array $line_items) {
    foreach ($line_items as $line_item) {
      $this->setLineItem($line_item);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLineItem(PaymentLineItemInterface $line_item) {
    $line_item->setPaymentId($this->id());
    $this->lineItems[$line_item->getName()] = $line_item;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetLineItem($name) {
    unset($this->lineItems[$name]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineItems() {
    return $this->lineItems;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineItem($name) {
    return isset($this->lineItems[$name]) ? $this->lineItems[$name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineItemsByType($plugin_id) {
    $line_items = array();
    foreach ($this->getLineItems() as $line_item) {
      if ($line_item->getPluginId() == $plugin_id) {
        $line_items[$line_item->getName()] = $line_item;
      }
    }

    return $line_items;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatuses(array $statuses) {
    $this->statuses = array_values($statuses);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus(PluginPaymentStatusInterface $status, $notify = TRUE) {
    $previous_status = $this->getStatus();
    $status->setPaymentId($this->id());
    // Prevent duplicate statuses.
    if (!$this->getStatus() || $this->getStatus()->getPluginId() != $status->getPluginId()) {
      $this->statuses[] = $status;
    }
    if ($notify) {
      /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher */
      $event_dispatcher = \Drupal::service('event_dispatcher');
      $event = new PaymentStatusSet($this, $previous_status);
      $event_dispatcher->dispatch(PaymentEvents::PAYMENT_STATUS_SET, $event);
      // @todo Invoke Rules event.
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatuses() {
    return $this->statuses;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->statuses ? end($this->statuses) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethod(PluginPaymentMethodInterface $payment_method) {
    $this->method = $payment_method;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    return $this->method;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($id) {
    $this->owner[0]->setValue($id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $user) {
    $this->owner[0]->setValue($user->id());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->owner[0]->get('target_id')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->owner[0]->get('entity')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    /** @var \Drupal\currency\MathInterface $math */
    $math = \Drupal::service('currency.math');
    $total = 0;
    foreach ($this->getLineItems() as $line_item) {
      $total = $math->add($total, $line_item->getTotalAmount());
    }

    return $total;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $manager = PaymentServiceWrapper::statusManager();
    // Execute the payment.
    if ($this->getPaymentMethod()->executePaymentAccess(\Drupal::currentUser())) {
      $this->setStatus($manager->createInstance('payment_pending'));
      $this->getPaymentMethod()->executePayment($this);
    }
    else {
      $this->setStatus($manager->createInstance('payment_failed'));
      $this->getPaymentType()->resumeContext();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    $values += array(
      'ownerId' => (int) \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    /** @var \Drupal\payment\Entity\PaymentInterface[] $entities */
    foreach ($entities as $payment) {
      $payment->getPaymentMethod()->setPayment($payment);
    }
    /** @var \Drupal\payment\Entity\Payment\PaymentStorageInterface $storage */
    $storage->loadLineItems($entities);
    $storage->loadPaymentStatuses($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    /** @var \Drupal\payment\Entity\Payment\PaymentStorageInterface $storage */
    $storage->saveLineItems(array(
      $this->id() => $this->getLineItems(),
    ));
    $storage->savePaymentStatuses(array(
      $this->id() => $this->getStatuses(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    /** @var \Drupal\payment\Entity\Payment\PaymentStorageInterface $storage */
    $storage->deleteLineItems(array_keys($entities));
    $storage->deletePaymentStatuses(array_keys($entities));
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['bundle'] = FieldDefinition::create('string')
      ->setLabel(t('Payment type'))
      ->setReadOnly(TRUE);
    $fields['currency'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Currency'))
      ->setDefaultValue(0)
      ->setSettings(array(
        'target_type' => 'currency',
      ));
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Payment ID'))
      ->setReadOnly(TRUE);
    $fields['owner'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDefaultValue(0)
      ->setSettings(array(
        'target_type' => 'user',
      ));
    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('Universally Unique ID'))
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * Clones the instance.
   */
  function __clone() {
    if ($this->getPaymentMethod()) {
      $this->setPaymentMethod(clone $this->getPaymentMethod());
    }
    $this->type = clone $this->getPaymentType();
  }
}
