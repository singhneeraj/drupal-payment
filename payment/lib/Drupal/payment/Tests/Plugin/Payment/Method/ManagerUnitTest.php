<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\Method\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\payment\Plugin\Payment\Method\Manager;
use Drupal\Tests\UnitTestCase;
use Zend\Stdlib\ArrayObject;

/**
 * Tests \Drupal\payment\Plugin\Payment\Method\Manager.
 */
class ManagerUnitTest extends UnitTestCase {

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
   * The plugin factory used for testing.
   *
   * @var \Drupal\Core\Language\LanguageManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The payment method plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager
   */
  public $paymentMethodManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Method\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->discovery = $this->getMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');

    $this->factory = $this->getMock('\Drupal\Component\Plugin\Factory\FactoryInterface');

    $this->languageManager = $this->getMockBuilder('\Drupal\Core\Language\LanguageManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $namespaces = new ArrayObject();

    $this->paymentMethodManager = new Manager($namespaces, $this->cache, $this->languageManager, $this->moduleHandler);
    $property = new \ReflectionProperty($this->paymentMethodManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentMethodManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentMethodManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentMethodManager, $this->factory);
  }

  /**
   * Tests createInstance().
   */
  public function testCreateInstance() {
    $existing_plugin_id = 'payment_unavailable';
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
    $this->paymentMethodManager->createInstance($non_existing_plugin_id);
    $this->paymentMethodManager->createInstance($existing_plugin_id);
  }

  /**
   * Tests getDefinitions().
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
      ->with('payment_method');
    $this->assertSame($definitions, $this->paymentMethodManager->getDefinitions());
  }

  /**
   * Tests options().
   *
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
    $this->assertSame($expected_options, $this->paymentMethodManager->options());
  }
}
