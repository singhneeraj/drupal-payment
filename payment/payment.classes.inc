<?php

/**
 * @file
 * The API and related functions for executing and managing payments.
 */

/**
 * A payment method that essentially disables payments.
 *
 * This is a 'placeholder' method that returns defaults and doesn't really do
 * anything else. It is used when no working payment method is available, so
 * other modules don't have to check for that.
 */
class PaymentMethodUnavailable extends PaymentMethod {

  public $enabled = FALSE;
  public $name = 'payment_method_unavailable';
  public $module = 'payment';
  // Use 0, so the payment method can be used for payments, but will never
  // collide with existing payment methods.
  public $pmid = 0;

  function __construct() {
    $this->title_specific = $this->title_generic = t('Unavailable');
    $this->controller = \Drupal::service('plugin.manager.payment.payment_method')->createInstance('payment_unavailable');
  }
}

/**
 * A Payment-related exception.
 */
class PaymentException extends Exception {

  /**
   * Implements Exception::__construct().
   */
  function __construct($message = '', $code = 0, Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
    payment_debug($this->getMessage(), $this->getFile(), $this->getLine());
  }
}

/**
 * Exception thrown if a payment is not valid.
 */
class PaymentValidationException extends PaymentException {}

/**
 * Exception thrown if a payment's total amount is below the required minimum.
 */
class PaymentValidationAmountBelowMinimumException extends PaymentValidationException {}

/**
 * Exception thrown if a payment's total amount exceeds the supported maximum.
 */
class PaymentValidationAmountExceedsMaximumException extends PaymentValidationException {}

/**
 * Exception thrown if a payment has no currency set.
 */
class PaymentValidationMissingCurrencyException extends PaymentValidationException {}

/**
 * Exception thrown if a payment uses a currency that is unsupported by a
 * payment method.
 */
class PaymentValidationUnsupportedCurrencyException extends PaymentValidationException {}

/**
 * Exception thrown if a payment's finish callback is not set or the function
 * does not exist.
 */
class PaymentValidationMissingFinishCallback extends PaymentValidationException {}

/**
 * Exception thrown if a payment's description is missing.
 */
class PaymentValidationDescriptionMissing extends PaymentValidationException {}

/**
 * Exception thrown if a payment's description is too long.
 */
class PaymentValidationDescriptionTooLong extends PaymentValidationException {}

/**
 * Exception thrown if a payment method is disabled.
 */
class PaymentValidationPaymentMethodDisabledException extends PaymentValidationException {}