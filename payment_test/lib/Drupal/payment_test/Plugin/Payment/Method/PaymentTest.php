<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\Base;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test",
 *   label = @Translation("Test method")
 * )
 */
class PaymentTest extends Base {

  /**
   * {@inheritdoc}
   */
  protected function currencies() {
    return array();
  }
}
