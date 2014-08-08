<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Type\PaymentTypeManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Type;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManager;
use Drupal\Tests\UnitTestCase;
use Zend\Stdlib\ArrayObject;;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Type\PaymentTypeManager
 *
 * @group Payment
 */
class PaymentTypeManagerUnitTest extends UnitTestCase {

  /**
   * The cache backend used for testing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $classResolver;

  /**
   * The plugin discovery used for testing.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $discovery;

  /**
   * The plugin factory used for testing.
   *
   * @var \Drupal\Component\Plugin\Factory\FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $factory;

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManager
   */
  protected $paymentTypeManager;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->classResolver = $this->getMock('\Drupal\Core\DependencyInjection\ClassResolverInterface');

    $this->discovery = $this->getMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');

    $this->factory = $this->getMock('\Drupal\Component\Plugin\Factory\FactoryInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $namespaces = new ArrayObject();

    $this->paymentTypeManager = new PaymentTypeManager($namespaces, $this->cache, $this->moduleHandler, $this->classResolver);
    $property = new \ReflectionProperty($this->paymentTypeManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentTypeManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentTypeManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentTypeManager, $this->factory);
  }

  /**
   * @covers ::getDefinitions
   */
  public function testGetDefinitions() {
    $definitions = array(
      'foo' => array(
        'label' => $this->randomMachineName(),
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->moduleHandler->expects($this->once())
      ->method('alter')
      ->with('payment_type');
    $this->assertSame($definitions, $this->paymentTypeManager->getDefinitions());
  }

  /**
   * @covers ::createInstance
   */
  public function testCreateInstance() {
    $existing_plugin_id = 'payment_unavailable';
    $non_existing_plugin_id = $this->randomMachineName();
    $this->factory->expects($this->at(0))
      ->method('createInstance')
      ->with($non_existing_plugin_id)
      ->will($this->throwException(new PluginException()));
    $this->factory->expects($this->at(1))
      ->method('createInstance')
      ->with($existing_plugin_id);
    $this->factory->expects($this->at(2))
      ->method('createInstance')
      ->with($existing_plugin_id);
    $this->paymentTypeManager->createInstance($non_existing_plugin_id);
    $this->paymentTypeManager->createInstance($existing_plugin_id);
  }
}
