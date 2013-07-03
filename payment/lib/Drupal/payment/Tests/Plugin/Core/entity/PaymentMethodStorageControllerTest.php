<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Core\entity\PaymentMethodStorageControllerTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\entity;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\Core\entity\PaymentMethodInterface;
use Drupal\payment\Tests\Utility;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment\Plugin\Core\entity\PaymentMethodStorageController.
 */
class PaymentMethodStorageControllerTest extends WebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\Core\entity\PaymentMethodStorageController',
      'group' => 'Payment',
    );
  }

  /**
   * Tests create();
   */
  function testCreate() {
    $payment_method = entity_create('payment_method', array());
    $this->assertTrue($payment_method instanceof PaymentMethodInterface);
    $this->assertTrue(is_int($payment_method->getOwnerId()));
    $this->assertEqual(count($payment_method->validate()), 0);
  }

  /**
   * Tests save();
   */
  function testSave() {
    $payment_method = Utility::createPaymentmethod(1);
    $payment_method->save();
    $payment_method_loaded = entity_load_unchanged('payment_method', $payment_method->id());
    $this->assertTrue($payment_method_loaded instanceof PaymentMethodInterface);
    $this->assertTrue($payment_method_loaded->getPlugin() instanceof PluginPaymentMethodInterface);
  }

  /**
   * Tests delete();
   */
  function testDelete() {
    $payment_method = Utility::createPaymentMethod(1);
    $payment_method->save();
    $this->assertTrue($payment_method->id());
    $payment_method->delete();
    $this->assertFalse(entity_load('payment_method', $payment_method->id()));
  }
}