<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\status\BaseTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\line_item;

use Drupal\payment\Plugin\payment\line_item\LineItemInterface;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 */
class BaseTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\payment\line_item\Base',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->lineItem = \Drupal::service('plugin.manager.payment.line_item')->createInstance('payment_basic');
  }

  /**
   * Tests setAmount() and getAmount().
   */
  function testGetAmount() {
    $amount = 5.3;
    $this->assertTrue($this->lineItem->setAmount($amount) instanceof LineItemInterface);
    $this->assertIdentical($this->lineItem->getAmount(), $amount);
  }

  /**
   * Tests setQuantity() and getQuantity().
   */
  function testGetQuantity() {
    $quantity = 7;
    $this->assertTrue($this->lineItem->setQuantity($quantity) instanceof LineItemInterface);
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
    $this->assertTrue($this->lineItem->setName($name) instanceof LineItemInterface);
    $this->assertIdentical($this->lineItem->getName(), $name);
  }

  /**
   * Tests setPaymentId() and getPaymentId().
   */
  function testGetPaymentId() {
    $id = 7;
    $this->assertTrue($this->lineItem->setPaymentId($id) instanceof LineItemInterface);
    $this->assertIdentical($this->lineItem->getPaymentId(), $id);
  }
}
