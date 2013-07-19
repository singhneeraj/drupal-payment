<?php

/**
 * Contains \Drupal\payment\Plugin\payment\line_item\Base.
 */

namespace Drupal\payment\Plugin\payment\line_item;

use Drupal\Component\Plugin\PluginBase;
use Drupal\payment\Plugin\Core\Entity\PaymentInterface;
use Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface;

/**
 * A base line item.
 */
abstract class Base extends PluginBase implements PaymentLineItemInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += array(
      'amount' => 0,
      'currencyCode' => '',
      'name' => NULL,
      'paymentId' => NULL,
      'quantity' => 1,
    );
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentId($payment_id) {
    $this->configuration['paymentId'] = $payment_id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentId() {
    return $this->configuration['paymentId'];
  }

  /**
   * {@inheritdoc}
   */
  public function setAmount($amount) {
    $this->configuration['amount'] = $amount;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    return $this->configuration['amount'];
  }

  /**
   * {@inheritdoc}
   */
  function getTotalAmount() {
    return $this->getAmount() * $this->getQuantity();
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->configuration['name'] = $name;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyCode() {
    return $this->configuration['currencyCode'];
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyCode($currency_code) {
    $this->configuration['currencyCode'] = $currency_code;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->configuration['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function setQuantity($quantity) {
    $this->configuration['quantity'] = $quantity;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuantity() {
    return $this->configuration['quantity'];
  }
}
