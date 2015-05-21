<?php

/**
 * @file
 * Contains \Drupal\Tests\Payment\Unit\Plugin\Payment\DefaultPluginDefinitionMapperUnitTest.
 */

namespace Drupal\Tests\Payment\Unit\Plugin\Payment;

use Drupal\payment\Plugin\Payment\DefaultPluginDefinitionMapper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\DefaultPluginDefinitionMapper
 *
 * @group Payment
 */
class DefaultPluginDefinitionMapperUnitTest extends UnitTestCase {

  /**
   * The plugin definition.
   *
   * @var mixed[]
   */
  protected $pluginDefinition;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\DefaultPluginDefinitionMapper
   */
  protected $sut;

  public function setUp() {
    $this->pluginDefinition = [
      'id' => $this->randomMachineName(),
      'parent_id' => $this->randomMachineName(),
      'label' => $this->getRandomGenerator()->string(),
      'description' => $this->getRandomGenerator()->string(),
      'foo' => $this->getRandomGenerator()->string(),
    ];

    $this->sut = new DefaultPluginDefinitionMapper();
  }

  /**
   * @covers ::getPluginId
   */
  public function testGetPluginId() {
    $this->assertSame($this->pluginDefinition['id'], $this->sut->getPluginId($this->pluginDefinition));
  }

  /**
   * @covers ::getParentPluginId
   */
  public function testGetParentPluginId() {
    $this->assertSame($this->pluginDefinition['parent_id'], $this->sut->getParentPluginId($this->pluginDefinition));
  }

  /**
   * @covers ::getPluginLabel
   */
  public function testGetPluginLabel() {
    $this->assertSame($this->pluginDefinition['label'], $this->sut->getPluginLabel($this->pluginDefinition));

    unset($this->pluginDefinition['label']);

    $this->assertNull($this->sut->getPluginLabel($this->pluginDefinition));
  }

  /**
   * @covers ::getPluginDescription
   */
  public function testGetPluginDescription() {
    $this->assertSame($this->pluginDefinition['description'], $this->sut->getPluginDescription($this->pluginDefinition));

    unset($this->pluginDefinition['description']);

    $this->assertNull($this->sut->getPluginDescription($this->pluginDefinition));
  }

  /**
   * @covers ::hasPluginDefinitionProperty
   */
  public function testHasPluginDefinitionProperty() {
    $this->assertTrue($this->sut->hasPluginDefinitionProperty($this->pluginDefinition, 'foo'));
    $this->assertFalse($this->sut->hasPluginDefinitionProperty($this->pluginDefinition, $this->randomMachineName()));
  }

  /**
   * @covers ::getPluginDefinitionProperty
   */
  public function testGetPluginDefinitionPropertyWithExistingProperty() {
    $this->assertSame($this->pluginDefinition['foo'], $this->sut->getPluginDefinitionProperty($this->pluginDefinition, 'foo'));
  }

  /**
   * @covers ::getPluginDefinitionProperty
   *
   * @expectedException \InvalidArgumentException
   */
  public function testGetPluginDefinitionPropertyWithNonExistingProperty() {
    $this->sut->getPluginDefinitionProperty($this->pluginDefinition, $this->randomMachineName());
  }

}
