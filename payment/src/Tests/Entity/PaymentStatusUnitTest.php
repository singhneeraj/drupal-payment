<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentStatusUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentStatusInterface;
use Drupal\simpletest\KernelTestBase;

/**
 * \Drupal\payment\Entity\PaymentStatus unit test.
 *
 * @group Payment
 */
class PaymentStatusUnitTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * The payment status to test with.
   *
   * @var \Drupal\payment\Entity\PaymentStatusInterface
   */
  protected $status;

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->status = entity_create('payment_status', array());
  }

  /**
   * Tests id() and setId().
   */
  protected function testId() {
    $id = strtolower($this->randomName());
    if ($this->assertTrue($this->status->setId($id) instanceof PaymentStatusInterface)) {
      $this->assertIdentical($this->status->id(), $id);
    }
  }

  /**
   * Tests label() and setLabel().
   */
  protected function testLabel() {
    $label = $this->randomString();
    if ($this->assertTrue($this->status->setLabel($label) instanceof PaymentStatusInterface)) {
      $this->assertIdentical($this->status->label(), $label);
    }
  }

  /**
   * Tests getParentId() and setParentId().
   */
  protected function testGetParentId() {
    $id = strtolower($this->randomName());
    if ($this->assertTrue($this->status->setParentId($id) instanceof PaymentStatusInterface)) {
      $this->assertIdentical($this->status->getParentId(), $id);
    }
  }

  /**
   * Tests getDescription() and setDescription().
   */
  protected function testGetDescription() {
    $description = $this->randomString();
    if ($this->assertTrue($this->status->setDescription($description) instanceof PaymentStatusInterface)) {
      $this->assertIdentical($this->status->getDescription(), $description);
    }
  }
}
