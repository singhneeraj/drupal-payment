<?php

/**
 * @file
 * Contains class \Drupal\payment\Generate.
 */

namespace Drupal\payment;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;
use Drupal\Component\Utility\Random;

/**
 * Provides utility tools to support tests.
 */
class Generate {

  /**
   * Creates a payment.
   *
   * @param integer $uid
   *   The user ID of the payment's owner.
   * @param \Drupal\payment\Plugin\Core\entity\PaymentMethod $payment_method
   *   An optional payment method to set. Defaults to Unavailable.
   *
   * @return \Drupal\payment\Plugin\Core\entity\Payment
   */
  static function createPayment($uid, PaymentMethod $payment_method = NULL) {
    $line_item_manager = \Drupal::service('plugin.manager.payment.line_item');
    $context_manager = \Drupal::service('plugin.manager.payment.context');
    $payment = entity_create('payment', array())
      ->setPaymentMethodId('payment_unavailable')
      ->setOwnerId($uid)
      ->setPaymentContext($context_manager->createInstance('payment_unavailable'))
      ->setLineItem($line_item_manager->createInstance('payment_basic', array(
        'name' => 'foo',
        'amount' => 1.0,
      )))
      ->setLineItem($line_item_manager->createInstance('payment_basic', array(
        'name' => 'bar',
        'amount' => 2.0,
        'quantity' => 3,
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
   * @return \Drupal\payment\Plugin\Core\entity\PaymentMethod
   */
  static function createPaymentMethod($uid, PaymentMethodInterface $plugin = NULL) {
    $name = Random::name();
    $plugin = $plugin ? $plugin : \Drupal::service('plugin.manager.payment.payment_method')->createInstance('payment_unavailable', array(
      'foo' => 'bar',
    ));
    $payment_method = entity_create('payment_method', array())
      ->setId($name)
      ->setLabel($name)
      ->setOwnerId($uid)
      ->setPlugin($plugin);

    return $payment_method;
  }
}
