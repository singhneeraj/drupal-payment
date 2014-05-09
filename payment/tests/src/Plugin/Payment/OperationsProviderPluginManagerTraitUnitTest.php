<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Status\OperationsProviderPluginManagerTraitUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManager;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Stdlib\ArrayObject;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait
 */
class OperationsProviderPluginManagerTraitUnitTest extends UnitTestCase {

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $container;

  /**
   * The trait under test.
   *
   * @var \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait
   */
  public $trait;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
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
      'bar' => array(
        'id' => 'bar',
        'operations_provider' => '\Drupal\payment\Tests\Plugin\Payment\OperationsProviderPluginManagerTraitUnitTestOperationsProviderWithContainerInjection',
      ),
    );

    $this->trait = new OperationsProviderPluginManagerTraitUnitTestPluginManager($this->container, $plugin_definitions);

    $service = $this->randomName();
    $this->container->expects($this->any())
      ->method('get')
      ->with('foo')
      ->will($this->returnValue($service));

    $this->assertInstanceOf($plugin_definitions['foo']['operations_provider'], $this->trait->getOperationsProvider('foo'));
    /** @var \Drupal\payment\Tests\Plugin\Payment\OperationsProviderPluginManagerTraitUnitTestOperationsProviderWithContainerInjection $bar_operations_provider */
    $bar_operations_provider = $this->trait->getOperationsProvider('bar');
    $this->assertInstanceOf($plugin_definitions['bar']['operations_provider'], $bar_operations_provider);
    $this->assertSame($service, $bar_operations_provider->dependency);
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
  public function __construct(ContainerInterface $container, array $plugin_definitions) {
    $this->container = $container;
    $this->pluginDefinitions = $plugin_definitions;
  }

  /**
   * Returns a plugin definition.
   */
  protected function getDefinition($plugin_id) {
    return $this->pluginDefinitions[$plugin_id];
  }
}

class OperationsProviderPluginManagerTraitUnitTestOperationsProvider {

}

class OperationsProviderPluginManagerTraitUnitTestOperationsProviderWithContainerInjection implements ContainerInjectionInterface {

  /**
   * A dummy dependency.
   *
   * @var mixed
   */
  public $dependency;

  /**
   * Constructs a new class instance.
   *
   * @param mixed $dependency
   */
  public function __construct($dependency) {
    $this->dependency = $dependency;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('foo'));
  }

}