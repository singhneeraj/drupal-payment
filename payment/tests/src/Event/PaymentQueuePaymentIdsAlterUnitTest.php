<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentQueuePaymentIdsAlterUnitTest.
 */

namespace Drupal\payment\Tests\Event;

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
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->categoryId = $this->randomName();

    $this->ownerId = $this->randomName();

    $this->paymentIds = array($this->randomName());

    $this->event = new PaymentQueuePaymentIdsAlter($this->categoryId, $this->ownerId, $this->paymentIds);
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
    $payment_ids = array($this->randomName());
    $this->assertSame($this->event, $this->event->setPaymentIds($payment_ids));
    $this->assertSame($payment_ids, $this->event->getPaymentIds());

  }

}
