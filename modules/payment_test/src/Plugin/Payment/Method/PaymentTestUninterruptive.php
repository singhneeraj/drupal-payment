<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test_uninterruptive",
 *   label = @Translation("Test method (uninterruptive)"),
 *   message_text = "Foo",
 *   message_text_format = "plain_text"
 * )
 */
class PaymentTestUninterruptive extends PaymentTestInterruptive {

  /**
   * {@inheritdoc}
   */
  public function isPaymentExecutionInterruptive() {
    return FALSE;
  }

}
