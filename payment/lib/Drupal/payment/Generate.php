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
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  static function createPayment($uid) {
    $payment = entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    ))->setPaymentMethodId('payment_unavailable')
      ->setOwnerId($uid)
      ->setLineItems(static::createPaymentLineItems());

    return $payment;
  }

  /**
   * Creates payment line items.
   *
   * @return array
   *   Values are
   *   \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   *   objects.
   */
  static function createPaymentLineItems() {
    $line_item_manager = \Drupal::service('plugin.manager.payment.line_item');
    $line_items = array(
      $line_item_manager->createInstance('payment_basic', array())
        ->setName('foo')
        ->setAmount(9.9)
      // The Dutch guilder has 100 subunits, which is most common, but is no
      // longer in circulation.
        ->setCurrencyCode('NLG')
        ->setDescription(Random::string()),
      $line_item_manager->createInstance('payment_basic', array())
        ->setName('bar')
        ->setAmount(5.5)
      // The Japanese yen has 1000 subunits.
        ->setCurrencyCode('JPY')
        ->setQuantity(2)
        ->setDescription(Random::string()),
      $line_item_manager->createInstance('payment_basic', array())
        ->setName('baz')
        ->setAmount(1.1)
      // The Malagasy ariary has 5 subunits, which is non-decimal.
        ->setCurrencyCode('MGA')
        ->setQuantity(3)
        ->setDescription(Random::string()),
    );

    return $line_items;
  }

  /**
   * Creates a payment method.
   *
   * @param integer $uid
   *   The user ID of the payment method's owner.
   * @param \Drupal\payment\Plugin\payment\method\PaymentMethodInterface $plugin
   *   An optional plugin to set. Defaults to payment_unavailable.
   *
   * @return \Drupal\payment\Entity\PaymentMethod
   */
  static function createPaymentMethod($uid, PaymentMethodInterface $plugin = NULL) {
    $name = Random::name();
    $plugin = $plugin ? $plugin : \Drupal::service('plugin.manager.payment.method')->createInstance('payment_unavailable', array(
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
