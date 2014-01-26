<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Status\ConfigDerivativeUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Status;

use Drupal\payment\Plugin\Payment\Status\ConfigDerivative;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Status\ConfigDerivative
 */
class ConfigDerivativeUnitTest extends UnitTestCase {

  /**
   * The plugin deriver under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\ConfigDerivative
   */
  protected $deriver;

  /**
   * The payment status storage controller used for testing.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusStorage;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Status\ConfigDerivative unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentStatusStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageControllerInterface');

    $this->deriver = new ConfigDerivative($this->paymentStatusStorage);
  }

  /**
   * @covers ::getDerivativeDefinitions
   */
  public function testGetDerivativeDefinitions() {
    $status_a = $this->getMock('\Drupal\payment\Entity\PaymentStatusInterface');
    $status_a->expects($this->once())
      ->method('getDescription')
      ->will($this->returnValue($this->randomName()));
    $status_a->expects($this->once())
      ->method('id')
      ->will($this->returnValue($this->randomName()));
    $status_a->expects($this->once())
      ->method('label')
      ->will($this->returnValue($this->randomName()));
    $status_a->expects($this->once())
      ->method('getParentId')
      ->will($this->returnValue($this->randomName()));

    $status_b = $this->getMock('\Drupal\payment\Entity\PaymentStatusInterface');
    $status_b->expects($this->once())
      ->method('getDescription')
      ->will($this->returnValue($this->randomName()));
    $status_b->expects($this->once())
      ->method('id')
      ->will($this->returnValue($this->randomName()));
    $status_b->expects($this->once())
      ->method('label')
      ->will($this->returnValue($this->randomName()));
    $status_b->expects($this->once())
      ->method('getParentId')
      ->will($this->returnValue($this->randomName()));

    $this->paymentStatusStorage->expects($this->once())
      ->method('loadMultiple')
      ->will($this->returnValue(array($status_a, $status_b)));

    $derivatives = $this->deriver->getDerivativeDefinitions(array());
    $this->assertCount(2, $derivatives);
  }
}
