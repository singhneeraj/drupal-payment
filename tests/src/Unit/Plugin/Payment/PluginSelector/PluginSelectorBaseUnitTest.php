<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\PluginSelectorBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorBase
 *
 * @group Payment
 */
class PluginSelectorBaseUnitTest extends PluginSelectorBaseUnitTestBase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $configuration = [];
    $this->sut = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorBase')
      ->setConstructorArgs(array($configuration, $this->pluginId, $this->pluginDefinition))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $configuration = [];
    $this->sut = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorBase')
      ->setConstructorArgs(array($configuration, $this->pluginId, $this->pluginDefinition))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->sut->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame([], $this->sut->calculateDependencies());
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array($this->randomMachineName());
    $this->assertSame($this->sut, $this->sut->setConfiguration($configuration));
    $this->assertSame($configuration, $this->sut->getConfiguration());
  }

  /**
   * @covers ::setLabel
   * @covers ::getLabel
   */
  public function testGetLabel() {
    $label = $this->randomMachineName();
    $this->assertSame($this->sut, $this->sut->setLabel($label));
    $this->assertSame($label, $this->sut->getLabel());
  }

  /**
   * @covers ::setCollectPluginConfiguration
   * @covers ::getCollectPluginConfiguration
   */
  public function testGetCollectPluginConfiguration() {
    $collect = (bool) mt_rand(0, 1);
    $this->assertSame($this->sut, $this->sut->setCollectPluginConfiguration($collect));
    $this->assertSame($collect, $this->sut->getCollectPluginConfiguration());
  }

  /**
   * @covers ::setPreviouslySelectedPlugins
   * @covers ::getPreviouslySelectedPlugins
   */
  public function testGetPreviouslySelectedPlugins() {
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $this->sut->setPreviouslySelectedPlugins([$plugin]);
    $this->assertSame([$plugin], $this->sut->getPreviouslySelectedPlugins());
  }

  /**
   * @covers ::setKeepPreviouslySelectedPlugins
   * @covers ::getKeepPreviouslySelectedPlugins
   *
   * @depends testGetPreviouslySelectedPlugins
   */
  public function testGetKeepPreviouslySelectedPlugins() {
    $keep = (bool) mt_rand(0, 1);
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $this->sut->setPreviouslySelectedPlugins([$plugin]);
    $this->assertSame($this->sut, $this->sut->setKeepPreviouslySelectedPlugins($keep));
    $this->assertSame($keep, $this->sut->getKeepPreviouslySelectedPlugins());

    // Confirm that all previously selected plugins are removed.
    $this->sut->setPreviouslySelectedPlugins([$plugin]);
    $this->sut->setKeepPreviouslySelectedPlugins(FALSE);
    $this->assertEmpty($this->sut->getPreviouslySelectedPlugins());
  }

  /**
   * @covers ::setSelectedPlugin
   * @covers ::getSelectedPlugin
   */
  public function testGetSelectedPlugin() {
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $this->assertSame($this->sut, $this->sut->setSelectedPlugin($plugin));
    $this->assertSame($plugin, $this->sut->getSelectedPlugin());
  }

  /**
   * @covers ::setRequired
   * @covers ::isRequired
   */
  public function testGetRequired() {
    $this->assertFalse($this->sut->isRequired());
    $this->assertSame($this->sut, $this->sut->setRequired());
    $this->assertTrue($this->sut->isRequired());
    $this->sut->setRequired(FALSE);
    $this->assertFalse($this->sut->isRequired());
  }

}
