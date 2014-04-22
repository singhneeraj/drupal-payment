<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentStatusSetUnitTest.
 */

namespace Drupal\payment\Tests\Event;

use Drupal\payment\Event\PaymentStatusSet;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentStatusSet
 */
class PaymentStatusSetUnitTest extends UnitTestCase {

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentStatusSet
   */
  protected $event;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The previous payment status.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface|null
   */
  protected $previousPaymentStatus;

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\Event\PaymentStatusSet unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->event = new PaymentStatusSet($this->payment, $this->previousPaymentStatus);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

  /**
   * @covers ::getPreviousPaymentStatus
   */
  public function testGetPreviousPaymentStatus() {
    $this->assertSame($this->previousPaymentStatus, $this->event->getPreviousPaymentStatus());
  }

}
