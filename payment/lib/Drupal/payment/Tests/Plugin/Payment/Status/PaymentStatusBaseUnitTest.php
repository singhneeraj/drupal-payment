<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\Status\PaymentStatusBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Status;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase
 */
class PaymentStatusBaseUnitTest extends UnitTestCase {

  /**
   * The payment status plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  public $paymentStatusManager;

  /**
   * The ID of the payment status under test.
   *
   * @var string
   */
  public $pluginId;

  /**
   * The payment status under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase|\PHPUnit_Framework_MockObject_MockObject
   */
  public $status;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Status\PaymentStatusBase unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setup() {
    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $configuration = array();
    $this->pluginId = $this->randomName();
    $plugin_definition = array();
    $this->status = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Status\PaymentStatusBase')
      ->setConstructorArgs(array($configuration, $this->pluginId, $plugin_definition, $this->paymentStatusManager))
      ->setMethods(NULL)
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
    $this->assertNull($this->status->setConfiguration($configuration));
    $this->assertSame($configuration, $this->status->getConfiguration());
  }

  /**
   * @covers ::setCreated
   * @covers ::getCreated
   */
  public function testGetCreated() {
    $created = mt_rand();
    $this->assertSame(spl_object_hash($this->status), spl_object_hash($this->status->setCreated($created)));
    $this->assertSame($created, $this->status->getCreated());
  }

  /**
   * @covers ::setPaymentId
   * @covers ::getPaymentId
   */
  public function testGetPaymentId() {
    $created = mt_rand();
    $this->assertSame(spl_object_hash($this->status), spl_object_hash($this->status->setPaymentId($created)));
    $this->assertSame($created, $this->status->getPaymentId());
  }

  /**
   * @covers ::setId
   * @covers ::getId
   */
  public function testGetId() {
    $created = mt_rand();
    $this->assertSame(spl_object_hash($this->status), spl_object_hash($this->status->setId($created)));
    $this->assertSame($created, $this->status->getId());
  }

  /**
   * @covers ::getChildren
   */
  public function testGetChildren() {
    $children = array($this->randomName());
    $this->paymentStatusManager->expects($this->once())
      ->method('getChildren')
      ->with($this->pluginId)
      ->will($this->returnValue($children));
    $this->assertSame($children, $this->status->getChildren());
  }

  /**
   * @covers ::getDescendants
   */
  public function testGetDescendants() {
    $descendants = array($this->randomName());
    $this->paymentStatusManager->expects($this->once())
      ->method('getDescendants')
      ->with($this->pluginId)
      ->will($this->returnValue($descendants));
    $this->assertSame($descendants, $this->status->getDescendants());
  }

  /**
   * @covers ::getAncestors
   */
  public function testGetAncestors() {
    $ancestors = array($this->randomName());
    $this->paymentStatusManager->expects($this->once())
      ->method('getAncestors')
      ->with($this->pluginId)
      ->will($this->returnValue($ancestors));
    $this->assertSame($ancestors, $this->status->getAncestors());
  }

  /**
   * @covers ::hasAncestor
   */
  public function testHasAncestor() {
    $expected = TRUE;
    $this->paymentStatusManager->expects($this->once())
      ->method('hasAncestor')
      ->with($this->pluginId)
      ->will($this->returnValue($expected));
    $this->assertSame($expected, $this->status->hasAncestor($this->pluginId));
  }

  /**
   * @covers ::isOrHasAncestor
   */
  public function testIsOrHasAncestor() {
    $expected = TRUE;
    $this->paymentStatusManager->expects($this->once())
      ->method('isOrHasAncestor')
      ->with($this->pluginId)
      ->will($this->returnValue($expected));
    $this->assertSame($expected, $this->status->isOrHasAncestor($this->pluginId));
  }

  /**
   * @covers ::getOperations
   */
  public function testGetOperations() {
    $this->assertSame(array(), $this->status->getOperations($this->pluginId));
  }
}
