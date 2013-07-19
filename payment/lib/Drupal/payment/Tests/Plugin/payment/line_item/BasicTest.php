<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\status\BasicTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\line_item;

use Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Basic.
 */
class BasicTest extends WebTestBase {

  public static $modules = array('payment', 'payment_test');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\payment\line_item\Basic',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->manager = \Drupal::service('plugin.manager.payment.line_item');
    $this->lineItem = $this->manager->createInstance('payment_basic');
  }

  /**
   * Tests setAmount() and getAmount().
   */
  function testGetAmount() {
    $amount = 5.3;
    $this->assertTrue($this->lineItem->setAmount($amount) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getAmount(), $amount);
  }

  /**
   * Tests setQuantity() and getQuantity().
   */
  function testGetQuantity() {
    $quantity = 7;
    $this->assertTrue($this->lineItem->setQuantity($quantity) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getQuantity(), $quantity);
  }

  /**
   * Tests getTotalAmount().
   */
  function testGetTotalAmount() {
    $amount= 7;
    $quantity = 7;
    $this->lineItem->setAmount($amount);
    $this->lineItem->setQuantity($quantity);
    $this->assertIdentical($this->lineItem->getTotalAmount(), 49);
  }

  /**
   * Tests setName() and getName().
   */
  function testGetName() {
    $name = $this->randomName();
    $this->assertTrue($this->lineItem->setName($name) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getName(), $name);
  }

  /**
   * Tests setDescription() and getDescription().
   */
  function testGetDescription() {
    $description = $this->randomName();
    $this->assertTrue($this->lineItem->setDescription($description) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getDescription(), $description);
  }

  /**
   * Tests setPaymentId() and getPaymentId().
   */
  function testGetPaymentId() {
    $payment_id = mt_rand();
    $this->assertTrue($this->lineItem->setPaymentId($payment_id) instanceof PaymentLineItemInterface);
    $this->assertEqual($this->lineItem->getPaymentId(), $payment_id);
  }

  /**
   * Tests formElements().
   */
  function testFormElements() {
    $line_item_data = array(
      'line_item[amount][amount]' => '123.45',
      'line_item[quantity]' => '3',
      'line_item[description]' => 'Foo & Bar',
    );

    // Tests the presence of child form elements and their default values.
    $this->drupalGet('payment_test-plugin-payment-line_item-payment_basic');
    foreach (array_keys($line_item_data) as $name) {
      $this->assertFieldByName($name);
    }

    // Test valid values.
    $data = $line_item_data;
    $data['line_item[description]'] = 'FooBar';
    $this->drupalPost('payment_test-plugin-payment-line_item-payment_basic', $data, t('Submit'));
    $this->assertUrl('user', array(), 'Valid values trigger form submission.');

    // Test a non-integer quantity.
    $values =  array(
      'line_item[quantity]' => $this->randomName(2),
    );
    $this->drupalPost('payment_test-plugin-payment-line_item-payment_basic', $values, t('Submit'));
    $this->assertFieldByXPath('//input[@name="line_item[quantity]" and contains(@class, "error")]');
  }

  /**
   * Tests saveData(), loadData(), and deleteData().
   */
  public function testData() {
    $name = $this->randomName();
    $payment_id = mt_rand();
    $description = $this->randomString();
    $this->lineItem
      ->setDescription($description)
      ->setName($name)
      ->setPaymentId($payment_id)
      ->saveData()
      ->setDescription($this->randomString())
      ->loadData();
    $this->assertEqual($this->lineItem->getDescription(), $description);
    $this->lineItem
      ->setDescription($this->randomString())
      ->deleteData()
      ->loadData();
    $this->assertNotEqual($this->lineItem->getDescription(), $description);
  }

  /**
   * Tests integration with payment entities.
   */
  public function testPaymentEntityIntegration() {
    $description = $this->randomString();
    $name = $this->randomName();
    $payment = entity_create('payment', array());
    $this->lineItem
      ->setName($name)
      ->setDescription($description);
    $payment->setLineItem($this->lineItem)
      ->save();
    $this->assertNotNull($this->lineItem->getPaymentId());
    $payment_loaded = entity_load_unchanged('payment', $payment->id());
    $this->assertEqual($payment_loaded->getLineItem($name)->getDescription(), $description);
    $payment->delete();
    $this->lineItem
      ->setDescription($this->randomString())
      ->loadData();
    $this->assertFalse($this->lineItem->getDescription());
  }
}
