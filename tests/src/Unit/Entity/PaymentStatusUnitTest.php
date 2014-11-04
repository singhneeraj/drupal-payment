<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Entity\PaymentStatusUnitTest.
 */

namespace Drupal\Tests\payment\Entity;

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
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
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

  /**
   * @covers ::toArray
   */
  public function testToArray() {
    $config_prefix = $this->randomMachineName();

    $id = $this->randomMachineName();
    $label = $this->randomMachineName();
    $parent_id = mt_rand();
    $description = $this->randomMachineName();

    $expected_array = [
      'id' => $id,
      'label' => $label,
      'parentId' => $parent_id,
      'description' => $description,
    ];

    $this->status->setId($id);
    $this->status->setLabel($label);
    $this->status->setParentId($parent_id);
    $this->status->setDescription($description);

    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $entity_type->expects($this->atLeastOnce())
      ->method('getConfigPrefix')
      ->willReturn($config_prefix);
    $map = [
      ['id', 'id'],
      ['label', 'label'],
    ];
    $entity_type->expects($this->atLeastOnce())
      ->method('getKey')
      ->willReturnMap($map);

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->willReturn($entity_type);

    $this->typedConfigManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($config_prefix . '.' . $id)
      ->willReturn([
        'mapping' => [
          'uuid' => [],
        ],
      ]);

    $array = $this->status->toArray();
    $this->assertArrayHasKey('uuid', $array);
    unset($array['uuid']);
    $this->assertEquals($expected_array, $array);
  }

}
