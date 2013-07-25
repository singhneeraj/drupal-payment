<?php

/**
 * Contains \Drupal\payment\plugin\payment\line_item\PaymentLineItemInterface.
 */

namespace Drupal\payment\plugin\payment\line_item;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A payment line item.
 */
interface PaymentLineItemInterface extends PluginInspectionInterface {

  /**
   * Sets the ID of the payment the line item belongs to.
   *
   * @param $int $payment_id
   *
   * @return \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   */
  public function setPaymentId($payment_id);

  /**
   * Gets the ID of the payment this line item belongs to.
   *
   * @return int
   */
  public function getPaymentId();

  /**
   * Sets the amount.
   *
   * @param float $amount
   *
   * @return \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   */
  public function setAmount($amount);

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
   * @return \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
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
   * Sets the currency code.
   *
   * @param string $currency_code
   *
   * @return \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   */
  public function setCurrencyCode($currency_code);

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
   * @return \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   */
  public function setQuantity($quantity);

  /**
   * Gets the quantity.
   *
   * @return int
   */
  public function getQuantity();

  /**
   * Builds the form elements for this line item.
   *
   * @return array
   *   A render array.
   */
  public function formElements(array $form, array &$form_state);
}