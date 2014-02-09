<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\LineItem\PaymentLineItemBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\LineItem;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase
 */
class PaymentLineItemBaseUnitTest extends UnitTestCase {

  /**
   * The line item under test.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\Basic|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $lineItem;

  /**
   * The math service used for testing.
   *
   * @var \Drupal\currency\MathInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $math;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->math = $this->getMock('\Drupal\currency\MathInterface');

    $configuration = array();
    $plugin_id = $this->randomName();
    $plugin_definition = array();
    $this->lineItem = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase')
      ->setMethods(array('formElements', 'getConfigurationFromFormValues', 'getDescription'))
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->math))
      ->getMock();
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      $this->randomName() => mt_rand(),
    );
    $this->assertNull($this->lineItem->setConfiguration($configuration));
    $this->assertSame($configuration, $this->lineItem->getConfiguration());
  }

  /**
   * @covers ::setAmount
   * @covers ::getAmount
   */
  public function testGetAmount() {
    $amount = mt_rand();
    $this->assertSame(spl_object_hash($this->lineItem), spl_object_hash($this->lineItem->setAmount($amount)));
    $this->assertSame($amount, $this->lineItem->getAmount());
  }

  /**
   * @covers ::setQuantity
   * @covers ::getQuantity
   */
  public function testGetQuantity() {
    $quantity = 7;
    $this->assertSame(spl_object_hash($this->lineItem), spl_object_hash($this->lineItem->setQuantity($quantity)));
    $this->assertSame($quantity, $this->lineItem->getQuantity());
  }

  /**
   * @covers ::getTotalAmount
   * @depends testGetAmount
   * @depends testGetQuantity
   */
  public function testGetTotalAmount() {
    $amount= 7;
    $quantity = 7;
    $total_amount = 49;

    $this->math->expects($this->once())
      ->method('multiply')
      ->with($amount, $quantity)
      ->will($this->returnValue($total_amount));

    $this->lineItem->setAmount($amount);
    $this->lineItem->setQuantity($quantity);
    $this->assertSame($total_amount, $this->lineItem->getTotalAmount());
  }

  /**
   * @covers ::setName
   * @covers ::getName
   */
  public function testGetName() {
    $name = $this->randomName();
    $this->assertSame(spl_object_hash($this->lineItem), spl_object_hash($this->lineItem->setName($name)));
    $this->assertSame($name, $this->lineItem->getName());
  }

  /**
   * @covers ::setCurrencyCode
   * @covers ::getCurrencyCode
   */
  public function testGetCurrencyCode() {
    $currency_code = $this->randomName();
    $this->assertSame(spl_object_hash($this->lineItem), spl_object_hash($this->lineItem->setCurrencyCode($currency_code)));
    $this->assertSame($currency_code, $this->lineItem->getCurrencyCode());
  }

  /**
   * @covers ::setPaymentId
   * @covers ::getPaymentId
   */
  public function testGetPaymentId() {
    $payment_id = mt_rand();
    $this->assertSame(spl_object_hash($this->lineItem), spl_object_hash($this->lineItem->setPaymentId($payment_id)));
    $this->assertSame($payment_id, $this->lineItem->getPaymentId());
  }
}
