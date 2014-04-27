<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Hook\EntityCrudUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\payment\Hook\EntityCrud;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\EntityCrud
 */
class EntityCrudUnitTest extends UnitTestCase {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\EntityCrud.
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Hook\EntityCrud unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    // Because this is an integration test between the default managers, we
    // cannot mock the interfaces, but have to mock the classes.
    $this->paymentMethodManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->service = new EntityCrud($this->paymentMethodManager, $this->paymentStatusManager);
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $payment_method = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $payment_method->expects($this->any())
      ->method('getEntityTypeId')
      ->will($this->returnValue('payment_method_configuration'));

    $payment_status = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $payment_status->expects($this->any())
      ->method('getEntityTypeId')
      ->will($this->returnValue('payment_status'));

    $this->paymentMethodManager->expects($this->once())
      ->method('clearCachedDefinitions');

    $this->paymentStatusManager->expects($this->once())
      ->method('clearCachedDefinitions');

    $this->service->invoke($payment_method);
    $this->service->invoke($payment_status);
  }
}
