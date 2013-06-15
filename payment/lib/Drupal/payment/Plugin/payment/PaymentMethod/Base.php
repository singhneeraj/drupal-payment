<?php

/**
 * Contains \Drupal\payment\PaymentMethod.
 */

namespace Drupal\payment\Plugin\payment\PaymentMethod;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\Plugin\payment\PaymentMethod\PaymentMethodInterface;
use Drupal\payment\Plugin\Core\entity\Payment;
use Drupal\payment\Plugin\Core\entity\PaymentMethod;

/**
 * A base payment method controller.
 */
abstract class Base extends PluginBase implements PaymentMethodInterface {

  /**
   * The payment method this plugin is for.
   *
   * @var \Drupal\payment\PaymentProcessingInterface
   */
  protected $paymentMethod;

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentFormElements(array $form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(Payment $payment) {}

  /**
   * {@inheritdoc}
   */
  public function validatePayment(Payment $payment) {
    // Confirm the payment method is enabled, and thus available in general.
    if (!$this->getPaymentMethod()->status()) {
      throw new PaymentValidationPaymentMethodDisabledException(t('The payment method is disabled.'));
    }

    if (!$payment->currency_code) {
      throw new PaymentValidationMissingCurrencyException(t('The payment has no currency set.'));
    }

    $currencies = $this->getPaymentMethod()->controller->currencies;

    // Confirm the payment's currency is supported.
    $currencies = $this->currencies();
    if (!empty($currencies) && !isset($currencies[$payment->currency_code])) {
      throw new PaymentValidationUnsupportedCurrencyException(t('The currency is not supported by this payment method.'));
    }

    // Confirm the payment's description is set and valid.
    if (empty($payment->description)) {
      throw new PaymentValidationDescriptionMissing(t('The payment description is not set.'));
    }
    elseif (drupal_strlen($payment->description) > 255) {
      throw new PaymentValidationDescriptionTooLong(t('The payment description exceeds 255 characters.'));
    }

    // Confirm the finish callback is set and the function exists.
    if (empty($payment->finish_callback) || !function_exists($payment->finish_callback)) {
      throw new PaymentValidationMissingFinishCallback(t('The finish callback is not set or not callable.'));
    }

    // Confirm the payment amount is higher than the supported minimum.
    $minimum = isset($currencies[$payment->currency_code]['minimum']) ? $currencies[$payment->currency_code]['minimum'] : PAYMENT_MINIMUM_AMOUNT;
    if ($payment->totalAmount(TRUE) < $minimum) {
      throw new PaymentValidationAmountBelowMinimumException(t('The amount should be higher than !minimum.', array(
        '!minimum' => payment_amount_human_readable($minimum, $payment->currency_code),
      )));
    }

    // Confirm the payment amount does not exceed the maximum.
    if (isset($currencies[$payment->currency_code]['maximum']) && $payment->totalAmount(TRUE) > $currencies[$payment->currency_code]['maximum']) {
      throw new PaymentValidationAmountExceedsMaximumException(t('The amount should be lower than !maximum.', array(
        '!maximum' => payment_amount_human_readable($currencies[$payment->currency_code]['maximum'], $payment->currency_code),
      )));
    }

    // Invoke events.
    module_invoke_all('payment_validate', $payment, $this->getPaymentMethod());
    if (module_exists('rules')) {
      rules_invoke_event('payment_validate', $payment, $this->getPaymentMethod());
    }
  }
}