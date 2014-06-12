<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodSelector;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManager;
use Drupal\Tests\UnitTestCase;
use Zend\Stdlib\ArrayObject;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManager
 */
class PaymentMethodSelectorManagerUnitTest extends UnitTestCase {

  /**
   * The cache backend used for testing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  public $cache;

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
   * The payment method plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManager
   */
  public $paymentMethodSelectorManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->discovery = $this->getMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');

    $this->factory = $this->getMock('\Drupal\Component\Plugin\Factory\FactoryInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $namespaces = new ArrayObject();

    $this->paymentMethodSelectorManager = new PaymentMethodSelectorManager($namespaces, $this->cache, $this->moduleHandler);
    $property = new \ReflectionProperty($this->paymentMethodSelectorManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentMethodSelectorManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentMethodSelectorManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentMethodSelectorManager, $this->factory);
  }

  /**
   * @covers ::createInstance
   */
  public function testCreateInstance() {
    $existing_plugin_id = 'payment_select';
    $non_existing_plugin_id = $this->randomName();
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
    $this->paymentMethodSelectorManager->createInstance($non_existing_plugin_id);
    $this->paymentMethodSelectorManager->createInstance($existing_plugin_id);
  }

  /**
   * @covers ::getDefinitions
   */
  public function testGetDefinitions() {
    $definitions = array(
      'foo' => array(
        'label' => $this->randomName(),
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->moduleHandler->expects($this->once())
      ->method('alter')
      ->with('payment_method_selector');
    $this->assertSame($definitions, $this->paymentMethodSelectorManager->getDefinitions());
  }

  /**
   * @covers ::options
   * @depends testGetDefinitions
   */
  public function testOptions() {
    $label = $this->randomName();
    $definitions = array(
      'foo' => array(
        'label' => $label,
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $expected_options = array(
      'foo' => $label,
    );
    $this->assertSame($expected_options, $this->paymentMethodSelectorManager->options());
  }
}
