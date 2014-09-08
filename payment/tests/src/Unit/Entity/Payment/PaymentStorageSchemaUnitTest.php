<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Payment\PaymentStorageSchemaUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment;

use Drupal\payment\Entity\Payment\PaymentStorageSchema;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentStorageSchema
 *
 * @group Payment
 */
class PaymentStorageSchemaUnitTest extends UnitTestCase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $database;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\ContentEntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The storage field definitions.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject[]
   */
  protected $fieldStorageDefinitions;

  /**
   * The storage handler.
   *
   * @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $storage;

  /**
   * The storage schema under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStorageSchema
   */
  protected $storageSchema;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $entity_type_id_key = $this->randomMachineName();
    $entity_type_id = $this->randomMachineName();

    $this->database = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $this->fieldStorageDefinitions = array(
      $entity_type_id_key => $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface'),
    );

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->once())
      ->method('getFieldStorageDefinitions')
      ->with($entity_type_id)
      ->willReturn($this->fieldStorageDefinitions);

    $this->entityType = $this->getMock('\Drupal\Core\Entity\ContentEntityTypeInterface');
    $this->entityType->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($entity_type_id);

    $this->storage = $this->getMockBuilder('\Drupal\Core\Entity\Sql\SqlContentEntityStorage')
      ->disableOriginalConstructor()
      ->getMock();

    $this->storageSchema = new PaymentStorageSchema($this->entityManager, $this->entityType, $this->storage, $this->database);
  }

  /**
   * @covers ::alterEntitySchemaWithNonFieldColumns
   */
  public function testAlterEntitySchemaWithNonFieldColumns() {
    $schema = array(
      'payment' => array(
        'fields' => array(),
        'foreign keys' => array(),
      ),
    );
    $method = new \ReflectionMethod($this->storageSchema, 'alterEntitySchemaWithNonFieldColumns');
    $method->setAccessible(TRUE);
    $method->invokeArgs($this->storageSchema, array(&$schema));
    $this->assertInternalType('array', $schema);
    $this->assertArrayHasKey('payment', $schema);
    $this->assertInternalType('array', $schema['payment']);
    $this->assertArrayHasKey('fields', $schema['payment']);
    foreach ($schema['payment']['fields'] as $field) {
      $this->assertInternalType('array', $field);
      $this->assertArrayHasKey('type', $field);
    }
    $this->assertArrayHasKey('foreign keys', $schema['payment']);
  }

}
