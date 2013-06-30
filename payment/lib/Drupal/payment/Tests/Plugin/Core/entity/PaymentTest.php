<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Core\entity\PaymentTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\entity;

use Drupal\payment\Plugin\Core\entity\Payment;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\Core\entity\Payment.
 */
class PaymentTest extends DrupalUnitTestBase {

  public static $modules = array('payment', 'system');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\Core\entity\Payment',
      'group' => 'Payment',
    );
  }

  /**
   * Tests label().
   */
  function testLabel() {
    $payment = entity_create('payment', array());
    $this->assertIdentical($payment->label(), 'Payment ');
  }
}
