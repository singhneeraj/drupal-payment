<?php

/**
 * @file
 * Definition of Drupal\payment\Plugin\Core\Entity\Payment.
 */

namespace Drupal\payment\Plugin\Core\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Entity\EntityNG;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\payment\Plugin\Core\entity\PaymentInterface;
use Drupal\payment\Plugin\payment\line_item\LineItemInterface;
use Drupal\payment\Plugin\payment\status\PaymentStatusInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Defines a payment entity.
 *
 * @EntityType(
 *   base_table = "payment",
 *   controllers = {
 *     "access" = "Drupal\payment\Plugin\Core\entity\PaymentAccessController",
 *     "storage" = "Drupal\payment\Plugin\Core\entity\PaymentStorageController",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   fieldable = TRUE,
 *   id = "payment",
 *   label = @Translation("Payment"),
 *   module = "payment"
 * )
 */
class Payment extends EntityNG implements PaymentInterface {

  /**
   * Line items.
   *
   * @var array
   *   Keys are line item machine names. Values are
   *   Drupal\payment\Plugin\payment\line_item\LineItemInterface instances.
   */
  protected $lineItems = array();

  /**
   * Payment statuses.
   *
   * @var array
   *   Values are Drupal\payment\Plugin\payment\status\PaymentStatusInterface
   *   instances.
   */
  protected $statuses = array();

  /**
   * {@inheritdoc}
   */
  public function label($langcode = NULL) {
    // @todo Delegate this to the context plugin, once contexts have been
    // converted to plugins.
    return t('Payment !id', array(
      '!id' => $this->id(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentContext($context) {
    $this->set('paymentContext', $context);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentContext() {
    return $this->get('paymentContext')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyCode($currencyCode) {
    $this->set('currencyCode', $currencyCode);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyCode() {
    return $this->get('currencyCode')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFinishCallback($callback) {
    $this->finishCallback[0]->setValue($callback);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFinishCallback() {
    return $this->finishCallback[0]->value;
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
  public function setLineItem(LineItemInterface $line_item) {
    $line_item->setPaymentId($this->id());
    $this->lineItems[$line_item->getName()] = $line_item;

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
    foreach ($statuses as $status) {
      $this->setStatus($status, FALSE);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus(PaymentStatusInterface $status, $notify = TRUE) {
    $previousStatus = $this->getStatus();
    $status->setPaymentId($this->id());
    // Prevent duplicate statuses.
    if (!$this->getStatus() || $this->getStatus()->getPluginId() != $status->getPluginId()) {
      $this->statuses[] = $status;
    }
    if ($notify) {
      $handler = \Drupal::moduleHandler();
      foreach ($handler->getImplementations('payment_status_change') as $moduleName) {
        $handler->invoke($moduleName, 'payment_status_change', $this, $previousStatus);
        // If a hook invocation has added another log item, a new loop with
        // invocations has already been executed and we don't need to continue
        // with this one.
        if ($this->getStatus()->getPluginId() != $status->getPluginId()) {
          return;
        }
      }
      if ($handler->moduleExists('rules')) {
        rules_invoke_event('payment_status_change', $this, $previousStatus);
      }
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
    return end($this->statuses);
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethodId($id) {
    $this->paymentMethodId[0]->setValue($id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethodId() {
    return $this->paymentMethodId[0]->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    return entity_load('payment_method', $this->getPaymentMethodId());
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($id) {
    $this->ownerId[0]->setValue($id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->ownerId[0]->get('target_id')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->ownerId[0]->get('entity')->getValue();
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
  public function getAvailablePaymentMethods(array $paymentMethods = array()) {
    if (!$paymentMethods) {
      $paymentMethods = entity_load_multiple('payment_method');
    }
    $available = array();
    foreach ($paymentMethods as $paymentMethod) {
      try {
        $paymentMethod->validatePayment($this);
        $available[$paymentMethod->id()] = $paymentMethod;
      }
      catch (PaymentValidationException $e) {
      }
    }

    return $available;
  }

  /**
   * {@inheritdoc}
   */
  public function finish() {
    $this->save();
    $handler = \Drupal::moduleHandler();
    $handler->invokeAll('payment_pre_finish', $this);
    if ($handler->moduleExists('rules')) {
      rules_invoke_event('payment_pre_finish', $this);
    }
    call_user_func($this->finish_callback, $this);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $handler = \Drupal::moduleHandler();
    $manager = \Drupal::service('plugin.manager.payment.status');
    // Preprocess the payment.
    $handler->invokeAll('payment_pre_execute', $this);
    if ($handler->moduleExists('rules')) {
      rules_invoke_event('payment_pre_execute', $this);
    }
    // Execute the payment.
    if (count($this->validate())) {
      $this->setStatus($manager->createInstance('payment_failed'));
    }
    else {
      $this->setStatus($manager->createInstance('payment_pending'));
      $this->getPaymentMethod()->executePayment($this);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $violations = parent::validate();
    // Do not call the payment method if it does not exist, in which case a
    // violation will already have been added.
    if ($this->getPaymentMethodId()) {
      try {
        $this->getPaymentMethod()->validatePayment($this);
      }
      catch (PaymentValidationException $exception) {
        $violations->add(new ConstraintViolation($exception->getMessage(), $exception->getMessage(), array(), $this, 'paymentMethodId', $this->getPaymentMethodId()));
      }
    }

    return $violations;
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
}
