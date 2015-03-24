<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentPreRefundUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\payment\Event\PaymentPreRefund;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentPreRefund
 *
 * @group Payment
 */
class PaymentPreRefundUnitTest extends UnitTestCase {

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentPreRefund
   */
  protected $event;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->event = new PaymentPreRefund($this->payment);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->event = new PaymentPreRefund($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

}
