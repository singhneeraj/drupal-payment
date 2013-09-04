<?php

/**
 * Contains \Drupal\payment_test\Plugin\payment\method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\payment\method;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\payment\method\Base;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test",
 *   label = @Translation("Test method"),
 *   operations = {
 *     "access_denied" = {
 *       "label" = @Translation("Nobody has permission to perform this operation.")
 *     },
 *     "foo" = {
 *       "label" = @Translation("Foo")
 *     }
 *   }
 * )
 */
class PaymentTest extends Base {

  /**
   * {@inheritdoc}
   */
  public function currencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function brandOptions() {
    return array(
      'default' => 'Test method',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function paymentOperationAccess(PaymentInterface $payment, $operation, $payment_method_brand) {
    return $operation != 'access_denied';
  }

  /**
   * {@inheritdoc}
   */
  function executePaymentOperation(PaymentInterface $payment, $operation, $payment_method_brand) {
    \Drupal::state()->set('payment_test_execute_operation', $operation);
  }
}
