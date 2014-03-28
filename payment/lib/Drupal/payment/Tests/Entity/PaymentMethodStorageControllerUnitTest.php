<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentMethodStorageUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Generate;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Entity\PaymentMethodStorage.
 */
class PaymentMethodStorageUnitTest extends DrupalUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Entity\PaymentMethodStorage web test',
      'group' => 'Payment',
    );
  }

  /**
   * Tests create();
   */
  protected function testCreate() {
    /** @var \Drupal\payment\Entity\PaymentMethodInterface $payment_method */
    $payment_method = entity_create('payment_method', array());
    $this->assertTrue($payment_method instanceof PaymentMethodInterface);
    $this->assertTrue(is_int($payment_method->getOwnerId()));
  }

  /**
   * Tests save();
   */
  protected function testSave() {
    $payment_method = Generate::createPaymentmethod(1, 'payment_basic');
    $payment_method->save();
    $payment_method_loaded = entity_load_unchanged('payment_method', $payment_method->id());
    $this->assertTrue($payment_method_loaded instanceof PaymentMethodInterface);
  }

  /**
   * Tests delete();
   */
  protected function testDelete() {
    $payment_method = Generate::createPaymentMethod(1, 'payment_basic');
    $payment_method->save();
    $this->assertTrue($payment_method->id());
    $payment_method->delete();
    $this->assertFalse(entity_load('payment_method', $payment_method->id()));
  }
}
