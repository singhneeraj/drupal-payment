<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Entity\PaymentUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Payment;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface;
use Drupal\payment\Generate;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Entity\Payment.
 */
class PaymentUnitTest extends DrupalUnitTestBase {

  /**
   * The payment bundle to test with used for testing.
   *
   * @var string
   */
  protected $bundle = 'payment_unavailable';

  /**
   * The payment line item manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\Manager
   */
  protected $lineItemManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field', 'payment', 'payment_test', 'system');

  /**
   * The payment under test.
   *
   * @var \Drupal\payment\Entity\Payment
   */
  protected $payment;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  protected $statusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Entity\Payment unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->bundle = 'payment_unavailable';
    $this->lineItemManager = Payment::lineItemManager();
    $this->statusManager = Payment::statusManager();
    $this->payment = entity_create('payment', array(
      'bundle' => $this->bundle,
    ));
  }

  /**
   * Tests getChangedTime().
   */
  protected function testGetChangedTime() {
    $status = $this->statusManager->createInstance('payment_succes');
    $this->payment->setStatus($status);
    $this->assertIdentical($this->payment->getChangedTime(), $status->getCreated());
  }

  /**
   * Tests label().
   */
  protected function testLabel() {
    $this->assertIdentical($this->payment->label(), 'Unavailable');
  }

  /**
   * Tests bundle().
   */
  protected function testBundle() {
    $this->assertIdentical($this->payment->bundle(), $this->bundle);
  }

  /**
   * Tests getPaymentType().
   */
  protected function testGetPaymentType() {
    if ($this->assertTrue($this->payment->getPaymentType() instanceof PaymentTypeInterface)) {
      $this->assertIdentical($this->payment->getPaymentType()->getPluginId(), $this->bundle);
    }
  }

  /**
   * Tests setCurrencyCode() and getCurrencyCode().
   */
  protected function testGetCurrencyCode() {
    $currency_code = 'ABC';
    $this->assertTrue($this->payment->setCurrencyCode($currency_code) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getCurrencyCode(), $currency_code);
  }

  /**
   * Tests setLineItem() and getLineItem().
   */
  protected function testGetLineItem() {
    $line_item = $this->lineItemManager->createInstance('payment_basic');
    $line_item->setName($this->randomName());
    $this->assertTrue($this->payment->setLineItem($line_item) instanceof PaymentInterface);
    $this->assertIdentical(spl_object_hash($this->payment->getLineItem($line_item->getName())), spl_object_hash($line_item));
  }

  /**
   * Tests unsetLineItem().
   */
  protected function testUnsetLineItem() {
    $line_item = $this->lineItemManager->createInstance('payment_basic');
    $name = $this->randomName();
    $line_item->setName($name);
    $this->payment->setLineItem($line_item);
    $this->assertEqual(spl_object_hash($this->payment), spl_object_hash($this->payment->unsetLineItem($name)));
    $this->assertNull($this->payment->getLineItem($name));
  }

  /**
   * Tests setLineItems() and getLineItems().
   */
  protected function testGetLineItems() {
    $line_item_1 = $this->lineItemManager->createInstance('payment_basic');
    $line_item_1->setName($this->randomName());
    $line_item_2 = $this->lineItemManager->createInstance('payment_basic');
    $line_item_2->setName($this->randomName());
    $this->assertTrue(spl_object_hash($this->payment->setLineItems(array($line_item_1, $line_item_2))), spl_object_hash($this->payment));
    $line_items = $this->payment->getLineItems();
    if ($this->assertTrue(is_array($line_items))) {
      $this->assertEqual(spl_object_hash($line_items[$line_item_1->getName()]), spl_object_hash($line_item_1));
      $this->assertEqual(spl_object_hash($line_items[$line_item_2->getName()]), spl_object_hash($line_item_2));
    }
  }

  /**
   * Tests getLineItemsByType().
   */
  protected function testGetLineItemsByType() {
    $type = 'payment_basic';
    $line_item = $this->lineItemManager->createInstance($type);
    $this->assertEqual(spl_object_hash($this->payment->setLineItem($line_item)), spl_object_hash($this->payment));
    $line_items = $this->payment->getLineItemsByType($type);
    $this->assertEqual(spl_object_hash(reset($line_items)), spl_object_hash($line_item));
  }

  /**
   * Tests setStatus() and getStatus().
   */
  protected function testGetStatus() {
    $status = $this->statusManager->createInstance('payment_pending');
    $this->assertEqual(spl_object_hash($this->payment->setStatus($status, FALSE)), spl_object_hash($this->payment));
    $this->assertEqual(spl_object_hash($this->payment->getStatus()), spl_object_hash($status));
  }

  /**
   * Tests setStatuses() and getStatuses().
   */
  protected function testGetStatuses() {
    $statuses = array($this->statusManager->createInstance('payment_pending'), $this->statusManager->createInstance('payment_failed'));
    $this->assertEqual(spl_object_hash($this->payment->setStatuses($statuses)), spl_object_hash($this->payment));
    $retrieved_statuses = $this->payment->getStatuses();
    $this->assertEqual(spl_object_hash(reset($retrieved_statuses)), spl_object_hash(reset($statuses)));
    $this->assertEqual(spl_object_hash(end($retrieved_statuses)), spl_object_hash(end($statuses)));
  }

  /**
   * Tests getPaymentMethod().
   */
  protected function testGetPaymentMethod() {
    $payment_method = Payment::methodManager()->createInstance('payment_basic');
    $this->payment->setPaymentMethod($payment_method);
    $this->assertTrue(spl_object_hash($this->payment->getPaymentMethod()), spl_object_hash($this->payment));
  }

  /**
   * Tests setOwnerId() and getOwnerId().
   */
  protected function testGetOwnerId() {
    $id = 5;
    $this->assertTrue($this->payment->setOwnerId($id) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getOwnerId(), $id);
  }

  /**
   * Tests getAmount().
   */
  protected function testGetAmount() {
    $amount = 3;
    $quantity = 2;
    for ($i = 0; $i < 2; $i++) {
      $line_item = $this->lineItemManager->createInstance('payment_basic');
      $line_item->setName($this->randomName());
      $line_item->setAmount($amount);
      $line_item->setQuantity($quantity);
      $this->payment->setLineItem($line_item);
    }
    $this->assertIdentical($this->payment->getAmount(), 12);
  }
}
