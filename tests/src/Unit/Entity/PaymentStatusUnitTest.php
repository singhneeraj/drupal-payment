<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentStatusUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity;

use Drupal\payment\Entity\PaymentStatus;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus
 *
 * @group Payment
 */
class PaymentStatusUnitTest extends UnitTestCase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The payment status to test.
   *
   * @var \Drupal\payment\Entity\PaymentStatus
   */
  protected $status;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $typedConfigManager;

  /**
   * {@inheritdoc}
   *
   * @covers ::setEntityManager
   * @covers ::setTypedConfig
   */
  public function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->entityTypeId = $this->randomMachineName();

    $this->typedConfigManager = $this->getMock('\Drupal\Core\Config\TypedConfigManagerInterface');

    $this->status = new PaymentStatus([], $this->entityTypeId);
    $this->status->setEntityManager($this->entityManager);
    $this->status->setTypedConfig($this->typedConfigManager);
  }

  /**
   * @covers ::id
   * @covers ::setId
   */
  public function testId() {
    $id = strtolower($this->randomMachineName());
    $this->assertSame($this->status, $this->status->setId($id));
    $this->assertSame($id, $this->status->id());
  }

  /**
   * @covers ::setLabel
   * @covers ::label
   */
  public function testLabel() {
    $entity_type = $this->getMock('\Drupal\Core\Config\Entity\ConfigEntityTypeInterface');
    $entity_type->expects($this->atLeastOnce())
      ->method('getKey')
      ->with('label')
      ->willReturn('label');

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->willReturn($entity_type);

    $label = $this->randomMachineName();
    $this->assertSame($this->status, $this->status->setLabel($label));
    $this->assertSame($label, $this->status->label());
  }

  /**
   * @covers ::getParentId
   * @covers ::setParentId
   */
  public function testGetParentId() {
    $id = strtolower($this->randomMachineName());
    $this->assertSame($this->status, $this->status->setParentId($id));
    $this->assertSame($id, $this->status->getParentId());
  }

  /**
   * @covers ::getDescription
   * @covers ::setDescription
   */
  public function testGetDescription() {
    $description = $this->randomMachineName();
    $this->assertSame($this->status, $this->status->setDescription($description));
    $this->assertSame($description, $this->status->getDescription());
  }

  /**
   * @covers ::entityManager
   */
  public function testEntityManager() {
    $method = new \ReflectionMethod($this->status, 'entityManager');
    $method->setAccessible(TRUE);
    $this->assertSame($this->entityManager, $method->invoke($this->status));
  }

  /**
   * @covers ::getTypedConfig
   */
  public function testGetTypedConfig() {
    $method = new \ReflectionMethod($this->status, 'getTypedConfig');
    $method->setAccessible(TRUE);
    $this->assertSame($this->typedConfigManager, $method->invoke($this->status));
  }

}
