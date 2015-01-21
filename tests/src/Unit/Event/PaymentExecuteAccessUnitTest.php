<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentExecuteAccessUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\payment\Event\PaymentExecuteAccess;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentExecuteAccess
 *
 * @group Payment
 */
class PaymentExecuteAccessUnitTest extends UnitTestCase {

  /**
   * The account to check access for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $account;

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentExecuteAccess
   */
  protected $event;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment method.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethod;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethod = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $this->event = new PaymentExecuteAccess($this->payment, $this->paymentMethod, $this->account);
  }

  /**
   * @covers ::getAccount
   */
  public function testGetAccount() {
    $this->assertSame($this->account, $this->event->getAccount());
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->event->getPayment());
  }

  /**
   * @covers ::getPaymentMethod
   */
  public function testGetPaymentMethod() {
    $this->assertSame($this->paymentMethod, $this->event->getPaymentMethod());
  }

  /**
   * @covers ::getAccessResult
   * @covers ::setAccessResult
   */
  public function testGetAccessResultAllowed() {
    $result = new AccessResultAllowed();
    $this->assertSame($this->event, $this->event->setAccessResult($result));
    $this->assertTrue($this->event->getAccessResult()->isAllowed());
  }

  /**
   * @covers ::getAccessResult
   * @covers ::setAccessResult
   */
  public function testGetAccessResultForbidden() {
    $result = new AccessResultForbidden();
    $this->assertSame($this->event, $this->event->setAccessResult($result));
    $this->assertFalse($this->event->getAccessResult()->isAllowed());
  }

  /**
   * @covers ::getAccessResult
   * @covers ::setAccessResult
   */
  public function testGetAccessResultNeutral() {
    $result = new AccessResultNeutral();
    $this->assertSame($this->event, $this->event->setAccessResult($result));
    $this->assertTrue($this->event->getAccessResult()->isAllowed());
  }

}
