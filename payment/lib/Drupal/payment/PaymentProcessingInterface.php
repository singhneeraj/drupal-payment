<?php

/**
 * Contains \Drupal\payment\PaymentProcessingInterface.
 */

namespace Drupal\payment;

use Drupal\payment\Entity\PaymentInterface;

/**
 * Defines anything that can process payments.
 */
interface PaymentProcessingInterface {

  /**
   * Returns the supported currencies.
   *
   * @var array
   *   Keys are ISO 4217 currency codes. Values are associative arrays with
   *   keys "minimum" and "maximum", whose values are the minimum and maximum
   *   amount supported for the specified currency. Leave empty to allow all
   *   currencies.
   */
  public function currencies();

  /**
   * Returns the available brand names.
   *
   * Payment method plugins may represent payment processing services that
   * provide more than one 'brand' of payment method through which a payment can
   * be processed, such as a combination of different credit or debit card
   * brands and direct debit.
   *
   * @return array
   *  Keys are machine names. Values are human-readable brand names.
   */
  public function brandOptions();

  /**
   * Returns the form elements to configure payments.
   *
   * $form_state['payment'] contains the payment that is added or edited. All
   * payment-specific information should be added to it during element
   * validation. The payment will be saved automatically.
   *
   * @param array $form
   * @param array $form_state
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return array
   *   A render array.
   */
  public function paymentFormElements(array $form, array &$form_state, PaymentInterface $payment);

  /**
   * Checks access to execute a payment operation.
   *
   * @see \Drupal\payment\Annotations\PaymentMethod
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $operation
   *   See \Drupal\payment\Annotations\PaymentMethod::operations for the
   *   definition of available operations.
   * @param string $payment_method_brand
   *   See self::brandOptions for the available brands.
   *
   * @return bool
   */
  function paymentOperationAccess(PaymentInterface $payment, $operation, $payment_method_brand);

  /**
   * Executes a payment operation.
   *
   * @see \Drupal\payment\Annotations\PaymentMethod
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $operation
   *   See \Drupal\payment\Annotations\PaymentMethod::operations for the
   *   definition of available operations.
   * @param string $payment_method_brand
   *   See self::brandOptions for the available brands.
   */
  function executePaymentOperation(PaymentInterface $payment, $operation, $payment_method_brand);
}