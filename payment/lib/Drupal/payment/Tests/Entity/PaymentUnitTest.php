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
   * The payment bundle to test with.
   *
   * @var string
   */
  protected $bundle = 'payment_unavailable';

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\Manager
   */
  protected $lineItemManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field', 'payment', 'payment_test', 'system');

  /**
   * The payment to test with.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * The payment status manager.
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
    $this->assertIdentical($this->payment->getLineItem($line_item->getName()), $line_item);
  }

  /**
   * Tests setLineItems() and getLineItems().
   */
  protected function testGetLineItems() {
    $line_item_1 = $this->lineItemManager->createInstance('payment_basic');
    $line_item_1->setName($this->randomName());
    $line_item_2 = $this->lineItemManager->createInstance('payment_basic');
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
  protected function testGetLineItemsByType() {
    $type = 'payment_basic';
    $line_item = $this->lineItemManager->createInstance('basic');
    $this->assertTrue($this->payment->setLineItem($line_item) instanceof PaymentInterface);
    $this->assertEqual($this->payment->getLineItemsByType($type), array(
      $line_item->getName() => $line_item,
    ));
  }

  /**
   * Tests setStatus() and getStatus().
   */
  protected function testGetStatus() {
    $status = $this->statusManager->createInstance('payment_pending');
    $this->assertTrue($this->payment->setStatus($status, FALSE) instanceof PaymentInterface);
    $state = \Drupal::state();
    $this->assertEqual($state->get('payment_test_payment_status_set'), TRUE);
    $this->assertEqual(spl_object_hash($this->payment->getStatus()), spl_object_hash($status));
  }

  /**
   * Tests setStatuses() and getStatuses().
   */
  protected function testGetStatuses() {
    $statuses = array($this->statusManager->createInstance('payment_pending'), $this->statusManager->createInstance('payment_success'));
    $this->assertTrue($this->payment->setStatuses($statuses) instanceof PaymentInterface);
    foreach ($this->payment->getStatuses() as $i => $status) {
      $this->assertEqual(spl_object_hash($status), spl_object_hash($statuses[$i]));
    }
  }

  /**
   * Tests setPaymentMethodId() and getPaymentMethodId().
   */
  protected function testGetPaymentMethodId() {
    $id = 5;
    $this->assertTrue($this->payment->setPaymentMethodId($id) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getPaymentMethodId(), $id);
  }

  /**
   * Tests getPaymentMethod().
   */
  protected function testGetPaymentMethod() {
    $payment_method = Generate::createPaymentMethod(1);
    $payment_method->save();
    $this->payment->setPaymentMethodId($payment_method->id());
    $this->assertTrue($this->payment->getPaymentMethod() instanceof PaymentMethodInterface);
  }

  /**
   * Tests setPaymentMethodBrand() and getPaymentMethodBrand().
   */
  protected function testGetPaymentMethodBrand() {
    $brand_name = $this->randomName();
    $this->assertTrue($this->payment->setPaymentMethodBrand($brand_name) instanceof PaymentInterface);
    $this->assertIdentical($this->payment->getPaymentMethodBrand(), $brand_name);
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
