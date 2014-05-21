<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\QueueUnitTest.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\Queue;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Queue
 */
class QueueUnitTest extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $database;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The database connection.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The queue class under test.
   *
   * @var \Drupal\payment\Queue
   */
  protected $queue;

  /**
   * The unique ID of the queue (instance).|\PHPUnit_Framework_MockObject_MockObject
   *
   * @var string
   */
  protected $queueId;

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\Queue unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  function setUp() {
    parent::setUp();
    $this->database = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->queueId = $this->randomName();

    $this->queue = new Queue($this->queueId, $this->database, $this->moduleHandler, $this->eventDispatcher, $this->paymentStatusManager);
  }

  /**
   * @covers ::alterLoadedPaymentIds
   */
  public function testAlterLoadedPaymentIds() {
    $category_id = $this->randomName();
    $owner_id = $this->randomName();
    $payment_ids = array($this->randomName());

    $this->moduleHandler->expects($this->once())
      ->method('alter')
      ->with('payment_queue_payment_ids', $category_id, $owner_id, $payment_ids);

    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_QUEUE_PAYMENT_IDS_ALTER);

    $method = new \ReflectionMethod($this->queue, 'alterLoadedPaymentIds');
    $method->setAccessible(TRUE);
    $method->invoke($this->queue, $category_id, $owner_id, $payment_ids);
  }

}
