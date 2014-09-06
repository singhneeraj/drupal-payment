<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentQueuePaymentIdsAlterUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\payment\Event\PaymentQueuePaymentIdsAlter;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentQueuePaymentIdsAlter
 *
 * @group Payment
 */
class PaymentQueuePaymentIdsAlterUnitTest extends UnitTestCase {

  /**
   * The queue category ID.
   *
   * @var string
   */
  protected $categoryId;

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentQueuePaymentIdsAlter
   */
  protected $event;

  /**
   * The ID of the user that must own the payments.
   *
   * @var int
   */
  protected $ownerId;

  /**
   * The IDs of available payments as loaded by the queue.
   *
   * @var int[]
   */
  protected $paymentIds;

  /**
   * The queue ID.
   *
   * @var string
   */
  protected $queueId;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->categoryId = $this->randomMachineName();

    $this->ownerId = $this->randomMachineName();

    $this->paymentIds = array($this->randomMachineName());

    $this->event = new PaymentQueuePaymentIdsAlter($this->queueId, $this->categoryId, $this->ownerId, $this->paymentIds);
  }

  /**
   * @covers ::getQueueId
   */
  public function testGetQueueId() {
    $this->assertSame($this->queueId, $this->event->getQueueId());
  }

  /**
   * @covers ::getCategoryId
   */
  public function testGetCategoryId() {
    $this->assertSame($this->categoryId, $this->event->getCategoryId());
  }

  /**
   * @covers ::getOwnerId
   */
  public function testGetOwnerId() {
    $this->assertSame($this->ownerId, $this->event->getOwnerId());
  }

  /**
   * @covers ::getPaymentIds
   * @covers ::setPaymentIds
   */
  public function testGetPaymentIds() {
    $this->assertSame($this->paymentIds, $this->event->getPaymentIds());
    $payment_ids = array($this->randomMachineName());
    $this->assertSame($this->event, $this->event->setPaymentIds($payment_ids));
    $this->assertSame($payment_ids, $this->event->getPaymentIds());

  }

}
