<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\DatabaseQueueUnitTest.
 */

namespace Drupal\Tests\payment\Unit;

use Drupal\payment\DatabaseQueue;
use Drupal\payment\Event\PaymentEvents;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\DatabaseQueue
 *
 * @group Payment
 */
class DatabaseQueueUnitTest extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $database;

  /**
   * The event dispatcher.
   *
   * @var \Drupal\payment\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The database connection.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The queue class under test.
   *
   * @var \Drupal\payment\DatabaseQueue
   */
  protected $queue;

  /**
   * The unique ID of the queue (instance).
   *
   * @var string
   */
  protected $queueId;

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();
    $this->database = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $this->eventDispatcher = $this->getMock('\Drupal\payment\EventDispatcherInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->queueId = $this->randomMachineName();

    $this->queue = new DatabaseQueue($this->queueId, $this->database, $this->eventDispatcher, $this->paymentStatusManager);
  }

  /**
   * @covers ::__construct
   */
  function testConstruct() {
    $this->queue = new DatabaseQueue($this->queueId, $this->database, $this->eventDispatcher, $this->paymentStatusManager);
  }

  /**
   * @covers ::getClaimExpirationPeriod
   * @covers ::setClaimExpirationPeriod
   */
  public function testGetClaimExpirationPeriod() {
    $expiration_period = mt_rand();
    $this->assertSame($this->queue, $this->queue->setClaimExpirationPeriod($expiration_period));
    $this->assertSame($expiration_period, $this->queue->getClaimExpirationPeriod());
  }

  /**
   * @covers ::getAllowedPaymentStatusIds
   * @covers ::setAllowedPaymentStatusIds
   */
  public function testGetAllowedPaymentStatusIds() {
    $allowed_payment_status_ids = array($this->randomMachineName(), $this->randomMachineName());
    $this->assertSame($this->queue, $this->queue->setAllowedPaymentStatusIds($allowed_payment_status_ids));
    $this->assertSame($allowed_payment_status_ids, $this->queue->getAllowedPaymentStatusIds());
  }

  /**
   * @covers ::claimPayment
   */
  public function testClaimPayment() {
    $payment_id = mt_rand();
    $acquisition_code = $this->randomMachineName();

    /** @var \Drupal\payment\DatabaseQueue|\PHPUnit_Framework_MockObject_MockObject $queue */
    $queue = $this->getMockBuilder('\Drupal\payment\DatabaseQueue')
      ->setConstructorArgs(array($this->queueId, $this->database, $this->eventDispatcher, $this->paymentStatusManager))
      ->setMethods(array('tryClaimPaymentOnce'))
      ->getMock();
    $queue->expects($this->at(0))
      ->method('tryClaimPaymentOnce')
      ->with($payment_id)
      ->will($this->returnValue(FALSE));
    $queue->expects($this->at(1))
      ->method('tryClaimPaymentOnce')
      ->with($payment_id)
      ->will($this->returnValue($acquisition_code));

    $this->assertSame($acquisition_code, $queue->claimPayment($payment_id));
  }

}
