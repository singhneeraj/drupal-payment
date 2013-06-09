<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Utility.
 */

namespace Drupal\payment\Tests;

use Drupal\Tests\UnitTestCase;

/**
 * Provides utility tools to support tests.
 */
class Utility {

  /**
   * Creates a payment.
   *
   * @todo port to D8.
   *
   * @param integer $uid
   *   The user ID of the payment's owner.
   * @param PaymentMethod $payment_method
   *   An optional payment method to set. Defaults to PaymentMethodUnavailable.
   *
   * @return Payment
   */
  static function createPayment($uid, PaymentMethod $payment_method = NULL) {
    $payment_method = $payment_method ? $payment_method : new PaymentMethodUnavailable;
    $payment = new Payment(array(
      'currency_code' => 'XXX',
      'description' => 'This is the payment description',
      'finish_callback' => 'payment_test_finish_callback',
      'method' => $payment_method,
      'uid' => $uid,
    ));
    $payment->setLineItem(new PaymentLineItem(array(
      'name' => 'foo',
      'amount' => 1.0,
      'tax_rate' => 0.1,
    )));

    return $payment;
  }

  /**
   * Creates a payment method.
   *
   * @param integer $uid
   *   The user ID of the payment method's owner.
   * @param PaymentMethodController $controller
   *   An optional controller to set. Defaults to
   *   PaymentMethodControllerUnavailable.
   *
   * @return PaymentMethod
   */
  static function createPaymentMethod($uid, PaymentMethodController $controller = NULL) {
    $name = UnitTestCase::randomName();
    $controller = $controller ? $controller : payment_method_controller_load('PaymentMethodControllerUnavailable');
    $payment_method = entity_create('payment_method', array(
      'controller' => $controller,
      'controller_data' => $controller->controller_data_defaults,
      'name' => $name,
      'label' => $name,
      'uid' => $uid,
    ));

    return $payment_method;
  }
}
