<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Core\Entity\PaymentStorageControllerWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\Entity;

use Drupal\payment\Plugin\Core\Entity\PaymentInterface;
use Drupal\payment\Plugin\payment\context\PaymentContextInterface;
use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment\Plugin\Core\Entity\PaymentStorageController.
 */
class PaymentStorageControllerWebTest extends WebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\Core\Entity\PaymentStorageController web test',
      'group' => 'Payment',
    );
  }

  /**
   * Tests create();
   */
  function testCreate() {
    $payment = entity_create('payment', array());
    $this->assertTrue($payment instanceof PaymentInterface);
    $this->assertTrue(is_int($payment->getOwnerId()));
    $this->assertEqual(count($payment->validate()), 0);
  }

  /**
   * Tests save();
   */
  function testSave() {
    $payment = Generate::createPayment(1);
    $this->assertFalse($payment->id());
    $payment->save();
    // @todo The ID should be an integer, but for some reason the entity field
    // API returns a string.
    $this->assertTrue(is_numeric($payment->id()));
    $this->assertTrue(strlen($payment->uuid()));
    $this->assertTrue(is_int($payment->getOwnerId()));
    $payment_loaded = entity_load_unchanged('payment', $payment->id());
    $this->assertEqual(count($payment_loaded->getLineItems()), count($payment->getLineItems()));
    $this->assertEqual(count($payment_loaded->getStatuses()), count($payment->getStatuses()));
    $this->assertTrue($payment_loaded->getPaymentContext() instanceof PaymentContextInterface);
  }

  /**
   * Tests delete();
   */
  function testDelete() {
    $payment = Generate::createPayment(1);
    $payment->save();
    $this->assertTrue($payment->id());
    $payment->delete();
    $this->assertFalse(entity_load('payment', $payment->id()));
    $line_items_exists = db_select('payment_line_item')
      ->condition('payment_id', $payment->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertFalse($line_items_exists);
    $statuses_exist = db_select('payment_status')
      ->condition('payment_id', $payment->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertFalse($statuses_exist);
  }
}
