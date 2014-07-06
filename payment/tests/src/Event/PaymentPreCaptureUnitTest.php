<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentPreCaptureUnitTest.
 */

namespace Drupal\payment\Tests\Event;

use Drupal\payment\Event\PaymentPreCapture;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentPreCapture
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
   */
  static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\Event\PaymentPreCapture unit test',
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

    $this->event = new PaymentPreCapture($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

}
