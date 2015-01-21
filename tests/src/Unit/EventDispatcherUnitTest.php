<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\EventDispatcherUnitTest.
 */

namespace Drupal\Tests\payment\Unit;

use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\EventDispatcher;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\EventDispatcher
 *
 * @group Payment
 */
class EventDispatcherUnitTest extends UnitTestCase {

  /**
   * The Symfony event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $symfonyEventDispatcher;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\EventDispatcher
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->symfonyEventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->eventDispatcher = new EventDispatcher($this->symfonyEventDispatcher);
  }

  /**
   * @covers ::alterQueueLoadedPaymentIds
   */
  public function testAlterQueueLoadedPaymentIds() {
    $queue_id = $this->randomMachineName();
    $category_id = $this->randomMachineName();
    $owner_id = mt_rand();
    $payment_ids = [mt_rand(), mt_rand(), mt_rand()];

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_QUEUE_PAYMENT_IDS_ALTER, $this->isInstanceOf('\Drupal\payment\Event\PaymentQueuePaymentIdsAlter'));

    $this->assertSame($payment_ids, $this->eventDispatcher->alterQueueLoadedPaymentIds($queue_id, $category_id, $owner_id, $payment_ids));
  }

  /**
   * {@inheritdoc}
   */
  public function testSetPaymentStatus() {
    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $previous_payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_STATUS_SET, $this->isInstanceOf('\Drupal\payment\Event\PaymentStatusSet'));

    $this->eventDispatcher->setPaymentStatus($payment, $previous_payment_status);
  }

  /**
   * {@inheritdoc}
   *
   */
  public function testPreExecutePayment() {
    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_PRE_EXECUTE, $this->isInstanceOf('\Drupal\payment\Event\PaymentPreExecute'));

    $this->eventDispatcher->preExecutePayment($payment);
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess() {
    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $account = $this->getMock('\Drupa\Core\Session\AccountInterface');

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_EXECUTE_ACCESS, $this->isInstanceOf('\Drupal\payment\Event\PaymentExecuteAccess'));

    $this->assertInstanceOf('\Drupal\Core\Access\AccessResultInterface', $this->eventDispatcher->executePaymentAccess($payment, $payment_method, $account));
  }

  /**
   * {@inheritdoc}
   *
   */
  public function preCapturePayment() {
    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_PRE_CAPTURE, $this->isInstanceOf('\Drupal\payment\Event\PaymentPreCapture'));

    $this->eventDispatcher->preCapturePayment($payment);
  }

  /**
   * {@inheritdoc}
   *
   */
  public function testPreRefundPayment() {
    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_PRE_REFUND, $this->isInstanceOf('\Drupal\payment\Event\PaymentPreRefund'));

    $this->eventDispatcher->preRefundPayment($payment);
  }

  /**
   * {@inheritdoc}
   */
  public function preResumeContext() {
    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $this->symfonyEventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_TYPE_PRE_RESUME_CONTEXT, $this->isInstanceOf('\Drupal\payment\Event\PreResumeContext'));

    $this->eventDispatcher->preResumeContext($payment);
  }

}
