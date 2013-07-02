<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Utility.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\payment\method\Unavailable;
use Drupal\Component\Utility\Random;

/**
 * Provides utility tools to support tests.
 */
class Utility {

  /**
   * Creates a payment.
   *
   * @param integer $uid
   *   The user ID of the payment's owner.
   * @param PaymentMethod $payment_method
   *   An optional payment method to set. Defaults to Unavailable.
   *
   * @return \Drupal\payment\Plugin\Core\entity\Payment
   */
  static function createPayment($uid, PaymentMethod $payment_method = NULL) {
    $lineItemManager = \Drupal::service('plugin.manager.payment.line_item');
    $payment = entity_create('payment', array());
    $payment->setFinishCallback('payment_test_finish_callback');
    $payment->setPaymentMethodId('payment_unavailable');
    $payment->setOwnerId($uid);
    $payment->setLineItem($lineItemManager->createInstance('payment_basic', array(
      'name' => 'foo',
      'amount' => 1.0,
    )));

    return $payment;
  }

  /**
   * Creates a payment method.
   *
   * @param integer $uid
   *   The user ID of the payment method's owner.
   * @param \Drupal\payment\Plugin\payment\method\PaymentMethodInterface $plugin
   *   An optional plugin to set. Defaults to payment_unavailable.
   *
   * @return PaymentMethod
   */
  static function createPaymentMethod($uid, PaymentMethodInterface $plugin = NULL) {
    $name = Random::name();
    $plugin = $plugin ? $plugin : \Drupal::service('plugin.manager.payment.payment_method')->createInstance('payment_unavailable');
    $payment_method = entity_create('payment_method', array())
      ->setId($name)
      ->setLabel($name)
      ->setOwnerId($uid)
      ->setPlugin($plugin);

    return $payment_method;
  }
}
