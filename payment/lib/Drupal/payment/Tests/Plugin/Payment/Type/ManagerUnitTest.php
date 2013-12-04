<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\Type\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Type;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\payment\Plugin\Payment\Type\Manager;
use Drupal\Tests\UnitTestCase;
use Zend\Stdlib\ArrayObject;;

/**
 * Tests \Drupal\payment\Plugin\Payment\Type\Manager.
 */
class ManagerUnitTest extends UnitTestCase {

  /**
   * The cache backend used for testing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

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
   * The plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\Manager
   */
  protected $paymentTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Type\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->discovery = $this->getMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');

    $this->factory = $this->getMock('\Drupal\Component\Plugin\Factory\FactoryInterface');

    $language = (object) array(
      'id' => $this->randomName(),
    );
    $this->languageManager = $this->getMockBuilder('\Drupal\Core\Language\LanguageManager')
      ->disableOriginalConstructor()
      ->getMock();
    $this->languageManager->expects($this->once())
      ->method('getLanguage')
      ->will($this->returnValue($language));

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $namespaces = new ArrayObject();

    $this->paymentTypeManager = new Manager($namespaces, $this->cache, $this->languageManager, $this->moduleHandler);
    $property = new \ReflectionProperty($this->paymentTypeManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentTypeManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentTypeManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentTypeManager, $this->factory);
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
      ->with('payment_type');
    $this->assertSame($definitions, $this->paymentTypeManager->getDefinitions());
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
    $this->paymentTypeManager->createInstance($non_existing_plugin_id);
    $this->paymentTypeManager->createInstance($existing_plugin_id);
  }
}
