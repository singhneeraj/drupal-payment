<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\Core\Url;
use Drupal\payment\PaymentExecutionResult;
use Drupal\payment\Response\Response;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test_response",
 *   label = @Translation("Test method (execution returns response)"),
 *   message_text = "Foo",
 *   message_text_format = "plain_text"
 * )
 */
class PaymentTestResponse extends PaymentTestNoResponse {

  /**
   * {@inheritdoc}
   */
  public function getPaymentExecutionResult() {
    return new PaymentExecutionResult(new Response(Url::fromUri('http://example.com')));
  }

}
