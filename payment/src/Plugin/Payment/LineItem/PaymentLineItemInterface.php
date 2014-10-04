<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface.
 */

namespace Drupal\payment\Plugin\Payment\LineItem;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A payment line item.
 */
interface PaymentLineItemInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Sets the payment the line item belongs to.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return static
   */
  public function setPayment(PaymentInterface $payment);

  /**
   * Gets the payment this line item belongs to.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment();

  /**
   * Gets the amount.
   *
   * @return float
   */
  public function getAmount();

  /**
   * Return this line item's total amount.
   *
   * @return float
   */
  function getTotalAmount();

  /**
   * Sets the machine name.
   *
   * @param string $name
   *
   * @return static
   */
  public function setName($name);

  /**
   * Gets the machine name.
   *
   * @return string
   */
  public function getName();

  /**
   * Gets the line item description.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Gets the currency_code.
   *
   * @return string
   */
  public function getCurrencyCode();

  /**
   * Sets the quantity.
   *
   * @param int|float $quantity
   *
   * @return static
   */
  public function setQuantity($quantity);

  /**
   * Gets the quantity.
   *
   * @return int|float
   */
  public function getQuantity();

}
