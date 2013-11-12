<?php

/**
 * Contains \Drupal\payment\PaymentProcessingInterface.
 */

namespace Drupal\payment;

use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Defines anything that can process payments.
 */
interface PaymentProcessingInterface {

  /**
   * Returns the available brand names.
   *
   * Payment method plugins may represent payment processing services that
   * provide more than one 'brand' of payment method through which a payment can
   * be processed, such as a combination of different credit or debit card
   * brands and direct debit.
   *
   * @return array
   *  Keys are machine names. Values are associative arrays with at least the
   *  following keys:
   *  - label (required): the translated human-readable label.
   *  - currencies (optional): An array of supported currencies. Keys are
   *    currency codes, and values are arrays with the following keys:
   *    - minimum (optional): the minimum amount for this currency.
   *    - maximum (optional): the maximum amount for this currency.
   *    Do not set this key to allow all currencies.
   */
  public function brands();

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
   * Checks if a payment can be executed.
   *
   * @see \Drupal\payment\Annotations\PaymentMethod
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $payment_method_brand
   *   See self::brandOptions for the available brands.
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  public function executePaymentAccess(PaymentInterface $payment, $payment_method_brand, AccountInterface $account);

  /**
   * Executes a payment.
   *
   * @see \Drupal\payment\Annotations\PaymentMethod
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   */
  public function executePayment(PaymentInterface $payment);
}