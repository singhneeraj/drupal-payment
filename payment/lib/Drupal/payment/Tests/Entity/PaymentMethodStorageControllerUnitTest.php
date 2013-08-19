<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Entity\PaymentMethodStorageControllerUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Generate;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Entity\PaymentMethodStorageController.
 */
class PaymentMethodStorageControllerUnitTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Entity\PaymentMethodStorageController web test',
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
    $manager = \Drupal::service('plugin.manager.payment.payment_method');
    $payment_method = Generate::createPaymentmethod(1, $manager->createInstance('payment_basic'));
    $payment_method->save();
    $payment_method_loaded = entity_load_unchanged('payment_method', $payment_method->id());
    $this->assertTrue($payment_method_loaded instanceof PaymentMethodInterface);
    $this->assertTrue($payment_method_loaded->getPlugin() instanceof PluginPaymentMethodInterface);
  }

  /**
   * Tests delete();
   */
  function testDelete() {
    $payment_method = Generate::createPaymentMethod(1);
    $payment_method->save();
    $this->assertTrue($payment_method->id());
    $payment_method->delete();
    $this->assertFalse(entity_load('payment_method', $payment_method->id()));
  }
}
