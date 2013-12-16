<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface.
 */

namespace Drupal\payment\Plugin\Payment\LineItem;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A payment line item.
 */
interface PaymentLineItemInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

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
   * Sets the amount.
   *
   * @param float $amount
   *
   * @return static
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
   * Sets the currency code.
   *
   * @param string $currency_code
   *
   * @return static
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
   * @return static
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
   * @param array $form
   * @param array $form_state
   *
   * @return array
   *   A render array.
   */
  public function formElements(array $form, array &$form_state);

  /**
   * Gets the plugin configuration from submitted form values.
   *
   * @param array $form
   *   The form elements as provided by self::formElements().
   * @param array $form_state
   *   The form's global state.
   *
   * @return array
   *   The exact same array as self::getConfiguration(), but with values from
   *   $form_state.
   */
  public static function getConfigurationFromFormValues(array $form, array &$form_state);
}
