<?php

/**
 * @file
 * Definition of Drupal\payment\PaymentMethodInterface.
 */

namespace Drupal\payment;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines payment methods.
 */
interface PaymentMethodInterface extends EntityInterface {

  /**
   * Validates a payment against this payment method.
   *
   * @param Payment $payment
   * @param boolean $strict
   *   Whether to validate everything a payment method needs or to validate the
   *   most important things only. Useful when finding available payment methods,
   *   for instance, which does not require unimportant things to be a 100%
   *   valid.
   *
   * @throws PaymentValidationException
   */
  public function validatePayment(\Payment $payment, $strict = TRUE);
}
