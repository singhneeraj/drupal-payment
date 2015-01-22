<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;

/**
 * Provides a plugin to select and configure a payment method for a payment.
 */
interface PaymentMethodSelectorInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Sets whether a payment method must be selected.
   *
   * @param bool $required
   *
   * @return $this
   */
  public function setRequired($required = TRUE);

  /**
   * Returns whether a payment method must be selected.
   *
   * @return bool
   */
  public function isRequired();

  /**
   * Sets which payment method plugins are allowed to be selected.
   *
   * @param array|true
   *   An array of payment method plugin IDs or TRUE to allow all.
   *
   * @return $this
   */
  public function setAllowedPaymentMethods($payment_method_plugin_ids);

  /**
   * Resets which payment method plugins are allowed to be selected.
   *
   * @return $this
   */
  public function resetAllowedPaymentMethods();

  /**
   * Returns the IDs of allowed payment method plugins.
   *
   * @return array|true
   *   An array of payment method plugin IDs or TRUE to allow all.
   */
  public function getAllowedPaymentMethods();

  /**
   * Gets the payment this payment method is for.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment();

  /**
   * Gets the payment this payment method is for.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return $this
   */
  public function setPayment(PaymentInterface $payment);

  /**
   * Gets the selected payment method.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  public function getPaymentMethod();

  /**
   * Sets the selected payment method.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface $payment_method
   *
   * @return $this
   */
  public function setPaymentMethod(PaymentMethodInterface $payment_method);

  /**
   * Returns all available payment methods.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[]
   *    An array of payment method plugin instances, keyed by plugin ID.
   */
  public function getAvailablePaymentMethods();

}
