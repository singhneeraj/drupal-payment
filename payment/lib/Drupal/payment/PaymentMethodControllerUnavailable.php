<?php

/**
 * Contains \Drupal\payment\PaymentMethodControllerUnavailable.
 */

namespace Drupal\payment;

use Drupal\payment\PaymentMethodController;

/**
 * A payment method controller that essentially disables payment methods.
 *
 * This is a 'placeholder' controller that returns defaults and doesn't really
 * do anything else. It is used when no working controller is available for a
 * payment method, so other modules don't have to check for that.
 */
class PaymentMethodControllerUnavailable extends PaymentMethodController {

  function __construct() {
    $this->title = t('Unavailable');
  }

  /**
   * Implements PaymentMethodController::execute().
   */
  function execute(Payment $payment) {
    $payment->setStatus(new PaymentStatusItem(PAYMENT_STATUS_UNKNOWN));
  }

  /**
   * Implements PaymentMethodController::validate().
   */
  function validate(Payment $payment, PaymentMethod $payment_method, $strict) {
    throw new PaymentValidationException(t('This payment method type is unavailable.'));
  }
}