<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\Core\Session\AccountInterface;
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

  /**
   * {@inheritdoc}
   */
  protected function doCapturePayment() {
  }

  /**
   * {@inheritdoc}
   */
  protected function doCapturePaymentAccess(AccountInterface $account) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function doRefundPayment() {
  }

  /**
   * {@inheritdoc}
   */
  protected function doRefundPaymentAccess(AccountInterface $account) {
    return FALSE;
  }

}
