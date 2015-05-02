<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentMethodConfigurationUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity;

use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfiguration
 *
 * @group Payment
 */
class PaymentMethodConfigurationUnitTest extends UnitTestCase {

  /**
   * The bundle.
   *
   * @var string
   */
  protected $bundle;

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
   * The payment method to test on.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfiguration
   */
  protected $paymentMethodConfiguration;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $typedConfigManager;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $userStorage;

  /**
   * {@inheritdoc}
   *
   * @covers ::setEntityManager
   * @covers ::setTypedConfig
   * @covers ::setUserStorage
   */
  public function setUp() {
    $this->bundle = $this->randomMachineName();

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->entityTypeId = $this->randomMachineName();

    $this->typedConfigManager = $this->getMock('\Drupal\Core\Config\TypedConfigManagerInterface');

    $this->userStorage = $this->getMock('\Drupal\user\UserStorageInterface');

    $this->paymentMethodConfiguration = new PaymentMethodConfiguration([
      'pluginId' => $this->bundle,
    ], $this->entityTypeId);
    $this->paymentMethodConfiguration->setEntityManager($this->entityManager);
    $this->paymentMethodConfiguration->setTypedConfig($this->typedConfigManager);
    $this->paymentMethodConfiguration->setUserStorage($this->userStorage);
  }

  /**
   * @covers ::bundle
   */
  public function testBundle() {
    $this->assertSame($this->bundle, $this->paymentMethodConfiguration->bundle());
  }

  /**
   * @covers ::getPluginId
   */
  public function testPluginId() {
    $this->assertSame($this->bundle, $this->paymentMethodConfiguration->getPluginId());
  }

  /**
   * @covers ::setPluginConfiguration
   * @covers ::getPluginConfiguration
   */
  public function testGetConfiguration() {
    $configuration = [$this->randomMachineName()];
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setPluginConfiguration($configuration));
    $this->assertSame($configuration, $this->paymentMethodConfiguration->getPluginConfiguration());
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
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setLabel($label));
    $this->assertSame($label, $this->paymentMethodConfiguration->label());
  }

  /**
   * @covers ::setOwnerId
   * @covers ::getOwnerId
   */
  public function testGetOwnerId() {
    $id = mt_rand();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setOwnerId($id));
    $this->assertSame($id, $this->paymentMethodConfiguration->getOwnerId());
  }

  /**
   * @covers ::getOwner
   *
   * @depends testGetOwnerId
   */
  public function testGetOwner() {
    $owner = $this->getMock('\Drupal\user\UserInterface');

    $id = mt_rand();

    $this->userStorage->expects($this->atLeastOnce())
      ->method('load')
      ->with($id)
      ->willReturn($owner);

    $this->paymentMethodConfiguration->setOwnerId($id);
    $this->assertSame($owner, $this->paymentMethodConfiguration->getOwner());
  }

  /**
   * @covers ::setOwner
   *
   * @depends testGetOwnerId
   */
  public function testSetOwner() {
    $id = mt_rand();

    $owner = $this->getMock('\Drupal\user\UserInterface');
    $owner->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($id);

    $this->paymentMethodConfiguration->setOwner($owner);
    $this->assertSame($id, $this->paymentMethodConfiguration->getOwnerId());
  }

  /**
   * @covers ::setId
   * @covers ::id
   */
  public function testId() {
    $id = mt_rand();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setId($id));
    $this->assertSame($id, $this->paymentMethodConfiguration->id());
  }

  /**
   * @covers ::setUuid
   * @covers ::uuid
   */
  public function testUuid() {
    $uuid = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setUuid($uuid));
    $this->assertSame($uuid, $this->paymentMethodConfiguration->uuid());
  }

  /**
   * @covers ::entityManager
   */
  public function testEntityManager() {
    $method = new \ReflectionMethod($this->paymentMethodConfiguration, 'entityManager');
    $method->setAccessible(TRUE);
    $this->assertSame($this->entityManager, $method->invoke($this->paymentMethodConfiguration));
  }

  /**
   * @covers ::getTypedConfig
   */
  public function testGetTypedConfig() {
    $method = new \ReflectionMethod($this->paymentMethodConfiguration, 'getTypedConfig');
    $method->setAccessible(TRUE);
    $this->assertSame($this->typedConfigManager, $method->invoke($this->paymentMethodConfiguration));
  }

  /**
   * @covers ::getUserStorage
   */
  public function testGetUserStorage() {
    $method = new \ReflectionMethod($this->paymentMethodConfiguration, 'getUserStorage');
    $method->setAccessible(TRUE);
    $this->assertSame($this->userStorage, $method->invoke($this->paymentMethodConfiguration));
  }

  /**
   * @covers ::toArray
   */
  public function testToArray() {
    $config_prefix = $this->randomMachineName();

    $id = $this->randomMachineName();
    $label = $this->randomMachineName();
    $owner_id = mt_rand();
    $plugin_configuration = [
      'foo' => $this->randomMachineName(),
    ];

    $expected_array = [
      'id' => $id,
      'label' => $label,
      'ownerId' => $owner_id,
      'pluginId' => $this->bundle,
      'pluginConfiguration' => $plugin_configuration,
    ];

    $this->paymentMethodConfiguration->setId($id);
    $this->paymentMethodConfiguration->setLabel($label);
    $this->paymentMethodConfiguration->setOwnerId($owner_id);
    $this->paymentMethodConfiguration->setPluginConfiguration($plugin_configuration);

    $entity_type = $this->getMock('\Drupal\Core\Config\Entity\ConfigEntityTypeInterface');
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

    $array = $this->paymentMethodConfiguration->toArray();
    $this->assertArrayHasKey('uuid', $array);
    unset($array['uuid']);
    $this->assertEquals($expected_array, $array);
  }

}
