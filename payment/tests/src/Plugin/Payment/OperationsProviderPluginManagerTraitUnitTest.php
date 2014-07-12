<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Status\OperationsProviderPluginManagerTraitUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait
 *
 * @group Payment
 */
class OperationsProviderPluginManagerTraitUnitTest extends UnitTestCase {

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $classResolver;

  /**
   * The trait under test.
   *
   * @var \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait
   */
  public $trait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->classResolver = $this->getMock('\Drupal\Core\DependencyInjection\ClassResolverInterface');
  }

  /**
   * @covers ::getOperationsProvider
   */
  public function testGetOperationsProvider() {
    $plugin_definitions = array(
      'foo' => array(
        'id' => 'foo',
        'operations_provider' => '\Drupal\payment\Tests\Plugin\Payment\OperationsProviderPluginManagerTraitUnitTestOperationsProvider',
      ),
    );

    $operations_provider = new \stdClass();

    $this->trait = new OperationsProviderPluginManagerTraitUnitTestPluginManager($this->classResolver, $plugin_definitions);

    $this->classResolver->expects($this->any())
      ->method('getInstanceFromDefinition')
      ->with($plugin_definitions['foo']['operations_provider'])
      ->will($this->returnValue($operations_provider));

    $this->assertSame($operations_provider, $this->trait->getOperationsProvider('foo'));
  }

}

class OperationsProviderPluginManagerTraitUnitTestPluginManager {

  use OperationsProviderPluginManagerTrait;

  /**
   * The plugin definitions.
   *
   * @var array
   */
  protected $pluginDefinitions = array();

  /**
   * Creates a new class instance.
   */
  public function __construct(ClassResolverInterface $class_resolver, array $plugin_definitions) {
    $this->classResolver = $class_resolver;
    $this->pluginDefinitions = $plugin_definitions;
  }

  /**
   * Returns a plugin definition.
   */
  protected function getDefinition($plugin_id) {
    return $this->pluginDefinitions[$plugin_id];
  }
}