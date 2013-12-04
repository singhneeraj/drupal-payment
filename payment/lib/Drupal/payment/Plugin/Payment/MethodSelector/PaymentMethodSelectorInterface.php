<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Provides a plugin to select and configure a payment method for a payment.
 */
interface PaymentMethodSelectorInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the form elements for selecting a payment method.
   *
   * @param array $form
   * @param array $form_state
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return array
   *   A render array.
   */
  public function formElements(array $form, array &$form_state, PaymentInterface $payment);

  /**
   * Gets selected payment method plugin.
   *
   * @param array $form
   *   The form elements as built by self::formElements().
   * @param array $form_state
   *   The form's global state.
   *
   * @return array
   */
  public function getPaymentMethodFromFormElements(array $form, array &$form_state);

  /**
   * Sets which payment method plugins are allowed to be selected.
   *
   * @param array
   *   An array of payment method plugin IDs.
   *
   * @return self
   */
  public function setAllowedPaymentMethods(array $payment_method_plugin_ids);

  /**
   * Resets which payment method plugins are allowed to be selected.
   *
   * @return self
   */
  public function resetAllowedPaymentMethods();

  /**
   * Returns the IDs of allowed payment method plugins.
   *
   * @return array|null
   *   An array of payment method plugin IDs or NULL to allow all.
   */
  public function getAllowedPaymentMethods();
}
