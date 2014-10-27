<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentPreCaptureUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\payment\Event\PaymentPreCapture;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentPreCapture
 *
 * @group Payment
 */
class PaymentPreCaptureUnitTest extends UnitTestCase {

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentPreCapture
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
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->event = new PaymentPreCapture($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

}
