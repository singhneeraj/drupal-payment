<?php

/**
 * @file
 * Contains class \Drupal\payment\Generate.
 */

namespace Drupal\payment;

use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\Component\Utility\Random;

/**
 * Provides utility tools to support tests.
 */
class Generate {

  /**
   * The random data generator.
   *
   * @var \Drupal\Component\Utility\Random
   */
  protected static $random;

  /**
   * Gets the random data generator.
   *
   * @return \Drupal\Component\Utility\Random
   */
  protected static function getRandom() {
    if (!static::$random) {
      static::$random = new Random();
    }

    return static::$random;
  }

  /**
   * Creates a payment.
   *
   * @param integer $uid
   *   The user ID of the payment's owner.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface|null $payment_method
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  static function createPayment($uid, PaymentMethodInterface $payment_method = NULL) {
    if (!$payment_method) {
      $payment_method = Payment::methodManager()->createInstance('payment_unavailable');
    }
    $payment = entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    ))->setCurrencyCode('EUR')
      ->setPaymentMethod($payment_method)
      ->setOwnerId($uid)
      ->setLineItems(static::createPaymentLineItems());

    return $payment;
  }

  /**
   * Creates payment line items.
   *
   * @return \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[]
   */
  static function createPaymentLineItems() {
    $line_item_manager = Payment::lineItemManager();
    $line_items = array(
      $line_item_manager->createInstance('payment_basic', array())
        ->setName('foo')
        ->setAmount(9.9)
      // The Dutch guilder has 100 subunits, which is most common, but is no
      // longer in circulation.
        ->setCurrencyCode('NLG')
        ->setDescription(static::getRandom()->string()),
      $line_item_manager->createInstance('payment_basic', array())
        ->setName('bar')
        ->setAmount(5.5)
      // The Japanese yen has 1000 subunits.
        ->setCurrencyCode('JPY')
        ->setQuantity(2)
        ->setDescription(static::getRandom()->string()),
      $line_item_manager->createInstance('payment_basic', array())
        ->setName('baz')
        ->setAmount(1.1)
      // The Malagasy ariary has 5 subunits, which is non-decimal.
        ->setCurrencyCode('MGA')
        ->setQuantity(3)
        ->setDescription(static::getRandom()->string()),
    );

    return $line_items;
  }

  /**
   * Creates a payment method.
   *
   * @param integer $uid
   *   The user ID of the payment method's owner.
   * @param string $plugin_id
   *   The ID of the payment method configuration plugin to use as the entity's
   *   bundle.
   *
   * @return \Drupal\payment\Entity\PaymentMethod
   */
  static function createPaymentMethod($uid, $plugin_id) {
    $name = static::getRandom()->name();

    $payment_method = entity_create('payment_method', array(
      'pluginId' => $plugin_id,
    ))
      ->setId($name)
      ->setLabel($name)
      ->setOwnerId($uid);

    return $payment_method;
  }
}
