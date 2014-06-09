<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodBase;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test",
 *   label = @Translation("Test method")
 * )
 */
class PaymentTest extends PaymentMethodBase {

  /**
   * {@inheritdoc}
   */
  protected function getSupportedCurrencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  protected function doExecutePayment() {
  }
}
