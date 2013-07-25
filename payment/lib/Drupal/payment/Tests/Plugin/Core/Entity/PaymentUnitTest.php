<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Core\Entity\PaymentUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\Entity;

use Drupal\payment\Plugin\Core\Entity\PaymentInterface;
use Drupal\payment\Plugin\Core\Entity\PaymentMethodInterface;
use Drupal\payment\Generate;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\Core\Entity\Payment.
 */
class PaymentUnitTest extends DrupalUnitTestBase {

  public static $modules = array('payment', 'system');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Core\Entity\Payment unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->payment = entity_create('payment', array());
  }

  /**
   * Tests label().
   */
  function testLabel() {
    $this->assertIdentical($this->payment->label(), 'Payment ');
  }

  /**
   * Tests setPaymentContext() and getPaymentContext().
   */
  function testGetPaymentContext() {
    $context = \Drupal::service('plugin.manager.payment.context')->createInstance('payment_unavailable');
    $this->assertTrue($this->payment->setPaymentContext($context) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getPaymentContext(), $context);
  }

  /**
   * Tests setCurrencyCode() and getCurrencyCode().
   */
  function testGetCurrencyCode() {
    $currency_code = 'ABC';
    $this->assertTrue($this->payment->setCurrencyCode($currency_code) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getCurrencyCode(), $currency_code);
  }

  /**
   * Tests setLineItem() and getLineItem().
   */
  function testGetLineItem() {
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    $line_item = $manager->createInstance('payment_basic');
    $line_item->setName($this->randomName());
    $this->assertTrue($this->payment->setLineItem($line_item) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getLineItem($line_item->getName()), $line_item);
  }

  /**
   * Tests setLineItems() and getLineItems().
   */
  function testGetLineItems() {
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    $line_item_1 = $manager->createInstance('payment_basic');
    $line_item_1->setName($this->randomName());
    $line_item_2 = $manager->createInstance('payment_basic');
    $line_item_2->setName($this->randomName());
    $this->assertTrue($this->payment->setLineItems(array($line_item_1, $line_item_2)) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getLineItems(), array(
      $line_item_1->getName() => $line_item_1,
      $line_item_2->getName() => $line_item_2,
    ));
  }

  /**
   * Tests getLineItemsByType().
   */
  function testGetLineItemsByType() {
    $type = 'payment_basic';
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    $line_item = $manager->createInstance('basic');
    $this->assertTrue($this->payment->setLineItem($line_item) instanceof PaymentInterface);
    $this->assertEqual($this->payment->getLineItemsByType($type), array(
      $line_item->getName() => $line_item,
    ));
  }

  /**
   * Tests setStatus() and getStatus().
   */
  function testGetStatus() {
    $manager = \Drupal::service('plugin.manager.payment.status');
    $status = $manager->createInstance('payment_created');
    // @todo Test notifications.
    $this->assertTrue($this->payment->setStatus($status, FALSE) instanceof PaymentInterface);
    $this->assertEqual($this->payment->getStatus(), $status);
  }

  /**
   * Tests setStatuses() and getStatuses().
   */
  function testGetStatuses() {
    $manager = \Drupal::service('plugin.manager.payment.status');
    $statuses = array($manager->createInstance('payment_created'), $manager->createInstance('payment_pending'));
    $this->assertTrue($this->payment->setStatuses($statuses) instanceof PaymentInterface);
    $this->assertEqual($this->payment->getStatuses(), $statuses);
  }

  /**
   * Tests setPaymentMethodId() and getPaymentMethodId().
   */
  function testGetPaymentMethodId() {
    $id = 5;
    $this->assertTrue($this->payment->setPaymentMethodId($id) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getPaymentMethodId(), $id);
  }

  /**
   * Tests getPaymentMethod().
   */
  function testGetPaymentMethod() {
    $payment_method = Generate::createPaymentMethod(1);
    $payment_method->save();
    $this->payment->setPaymentMethodId($payment_method->id());
    $this->assertTrue($this->payment->getPaymentMethod() instanceof PaymentMethodInterface);
  }

  /**
   * Tests setPaymentMethodBrand() and getPaymentMethodBrand().
   */
  function testGetPaymentMethodBrand() {
    $brand_name = $this->randomName();
    $this->assertTrue($this->payment->setPaymentMethodBrand($brand_name) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getPaymentMethodBrand(), $brand_name);
  }

  /**
   * Tests setOwnerId() and getOwnerId().
   */
  function testGetOwnerId() {
    $id = 5;
    $this->assertTrue($this->payment->setOwnerId($id) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getOwnerId(), $id);
  }

  /**
   * Tests getAmount().
   */
  function testGetAmount() {
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    $amount = 3;
    $quantity = 2;
    for ($i = 0; $i < 2; $i++) {
      $line_item = $manager->createInstance('payment_basic');
      $line_item->setName($this->randomName());
      $line_item->setAmount($amount);
      $line_item->setQuantity($quantity);
      $this->payment->setLineItem($line_item);
    }
    $this->assertIdentical($this->payment->getAmount(), 12);
  }
}
