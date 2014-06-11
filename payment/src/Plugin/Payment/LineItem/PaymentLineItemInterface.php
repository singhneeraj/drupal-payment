<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface.
 */

namespace Drupal\payment\Plugin\Payment\LineItem;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * A payment line item.
 */
interface PaymentLineItemInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Sets the ID of the payment the line item belongs to.
   *
   * @param int $payment_id
   *
   * @return static
   */
  public function setPaymentId($payment_id);

  /**
   * Gets the ID of the payment this line item belongs to.
   *
   * @return int
   */
  public function getPaymentId();

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
   * @param int $quantity
   *
   * @return static
   */
  public function setQuantity($quantity);

  /**
   * Gets the quantity.
   *
   * @return int
   */
  public function getQuantity();

}
