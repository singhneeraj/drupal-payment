<?php

/**
 * Contains \Drupal\payment\Plugin\payment\PaymentMethod\Unavailable.
 */

namespace Drupal\payment\Plugin\payment\PaymentMethod;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Plugin\Core\entity\Payment;

/**
 * A payment method controller that essentially disables payment methods.
 *
 * This is a 'placeholder' controller that returns defaults and doesn't really
 * do anything else. It is used when no working controller is available for a
 * payment method, so other modules don't have to check for that.
 *
 * @PaymentMethod(
 *   id = "payment_unavailable",
 *   label = @Translation("Unavailable"),
 *   module = "payment"
 * )
 */
class Unavailable extends Base {

  /**
   * {@inheritdoc}.
   */
  public function currencies() {
    return array();
  }

  /**
   * {@inheritdoc}.
   */
  public function executePayment(Payment $payment) {
    $payment->setStatus(new PaymentStatusItem(PAYMENT_STATUS_UNKNOWN));
  }

  /**
   * {@inheritdoc}.
   */
  public function validatePayment(Payment $payment) {
    throw new PaymentValidationException(t('This payment method plugin is unavailable.'));
  }
}
