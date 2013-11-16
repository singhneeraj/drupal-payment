<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\status\BaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\status;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 */
class BaseUnitTest extends UnitTestCase {

  /**
   * The payment status plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\payment\status\Manager|\PHPUnit_Framework_MockObject_MockObject
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
   * @var \Drupal\payment\Plugin\payment\status\Base|\PHPUnit_Framework_MockObject_MockObject
   */
  public $status;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\status\Base unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setup() {
    $this->paymentStatusManager = $this->getMockBuilder('\Drupal\payment\Plugin\payment\status\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $configuration = array();
    $this->pluginId = $this->randomName();
    $plugin_definition = array();
    $this->status = $this->getMockBuilder('\Drupal\payment\Plugin\payment\status\Base')
      ->setConstructorArgs(array($configuration, $this->pluginId, $plugin_definition, $this->paymentStatusManager))
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * Tests setCreated() and getCreated().
   */
  public function testGetCreated() {
    $created = mt_rand();
    $this->assertSame(spl_object_hash($this->status), spl_object_hash($this->status->setCreated($created)));
    $this->assertSame($created, $this->status->getCreated());
  }

  /**
   * Tests setPaymentId() and getPaymentId().
   */
  public function testGetPaymentId() {
    $created = mt_rand();
    $this->assertSame(spl_object_hash($this->status), spl_object_hash($this->status->setPaymentId($created)));
    $this->assertSame($created, $this->status->getPaymentId());
  }

  /**
   * Tests setId() and getId().
   */
  public function testGetId() {
    $created = mt_rand();
    $this->assertSame(spl_object_hash($this->status), spl_object_hash($this->status->setId($created)));
    $this->assertSame($created, $this->status->getId());
  }

  /**
   * Tests getChildren().
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
   * Tests getDescendants().
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
   * Tests getAncestors().
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
   * Tests hasAncestor().
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
   * Tests isOrHasAncestor().
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
   * Tests getOperations().
   */
  public function testGetOperations() {
    $this->assertInternalType('array', $this->status->getOperations($this->pluginId));
  }
}
