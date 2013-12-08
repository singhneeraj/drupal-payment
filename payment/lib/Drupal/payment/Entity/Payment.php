<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\Payment.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\payment\Payment as PaymentServiceWrapper;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface as PluginPaymentStatusInterface;

/**
 * Defines a payment entity.
 *
 * @EntityType(
 *   base_table = "payment",
 *   bundle_label = @Translation("Payment type"),
 *   controllers = {
 *     "access" = "Drupal\payment\Entity\PaymentAccessController",
 *     "form" = {
 *       "delete" = "Drupal\payment\Entity\PaymentDeleteFormController",
 *       "edit" = "Drupal\payment\Entity\PaymentEditFormController"
 *     },
 *     "list" = "Drupal\payment\Entity\PaymentListController",
 *     "view_builder" = "Drupal\payment\Entity\PaymentViewBuilder",
 *     "storage" = "Drupal\payment\Entity\PaymentStorageController",
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
 *     "edit-form" = "payment.payment.edit"
 *   },
 *   module = "payment"
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
   * Overrides Entity::__construct().
   */
  public function __construct(array $values, $entity_type, $bundle = FALSE, $translations = array()) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    PaymentServiceWrapper::typeManager()->clearCachedDefinitions();
    $this->type = PaymentServiceWrapper::typeManager()->createInstance($this->bundle());
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
    $previousStatus = $this->getStatus();
    $status->setPaymentId($this->id());
    // Prevent duplicate statuses.
    if (!$this->getStatus() || $this->getStatus()->getPluginId() != $status->getPluginId()) {
      $this->statuses[] = $status;
    }
    if ($notify) {
      $handler = \Drupal::moduleHandler();
      foreach ($handler->getImplementations('payment_status_set') as $moduleName) {
        $handler->invoke($moduleName, 'payment_status_set', array($this, $previousStatus));
        // If a hook invocation has added another log item, a new loop with
        // invocations has already been executed and we don't need to continue
        // with this one.
        if ($this->getStatus()->getPluginId() != $status->getPluginId()) {
          return;
        }
      }
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
    $total = 0;
    foreach ($this->getLineItems() as $line_item) {
      $total += $line_item->getTotalAmount();
    }

    return $total;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $manager = PaymentServiceWrapper::statusManager();
    // Execute the payment.
    if ($this->getPaymentMethod()->executePaymentAccess($this, \Drupal::currentUser())) {
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
  public static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    $values += array(
      'ownerId' => (int) \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $controller, $update = TRUE) {
    $controller->saveLineItems(array(
      $this->id() => $this->getLineItems(),
    ));
    $controller->savePaymentStatuses(array(
      $this->id() => $this->getStatuses(),
  ));
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageControllerInterface $controller, array $entities) {
    $controller->deleteLineItems(array_keys($entities));
    $controller->deletePaymentStatuses(array_keys($entities));
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $fields['currency'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Currency'))
      ->setFieldSettings(array(
        'target_type' => 'currency',
        'default_value' => 0,
      ));
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Payment ID'))
      ->setReadOnly(TRUE);
    $fields['owner'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setFieldSettings(array(
        'target_type' => 'user',
        'default_value' => 0,
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
