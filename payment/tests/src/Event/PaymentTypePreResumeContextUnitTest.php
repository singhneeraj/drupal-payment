<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentTypePreResumeContextUnitTest.
 */

namespace Drupal\payment\Tests\Event;

use Drupal\payment\Event\PaymentTypePreResumeContext;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentTypePreResumeContext
 */
class PaymentTypePreResumeContextUnitTest extends UnitTestCase {

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentTypePreResumeContext
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
      'name' => '\Drupal\payment\Event\PaymentTypePreResumeContext unit test',
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

    $this->event = new PaymentTypePreResumeContext($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

}
