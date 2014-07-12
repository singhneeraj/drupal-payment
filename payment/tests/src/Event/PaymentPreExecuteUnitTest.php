<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentPreExecuteUnitTest.
 */

namespace Drupal\payment\Tests\Event;

use Drupal\payment\Event\PaymentPreExecute;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentPreExecute
 *
 * @group Payment
 */
class PaymentPreExecuteUnitTest extends UnitTestCase {

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentPreExecute
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

    $this->event = new PaymentPreExecute($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

}
