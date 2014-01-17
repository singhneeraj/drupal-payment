<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\Status\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Status;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\payment\Plugin\Payment\Status\Manager;
use Drupal\Tests\UnitTestCase;
use Zend\Stdlib\ArrayObject;

/**
 * Tests \Drupal\payment\Plugin\Payment\Status\Manager.
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
   * The payment status plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  public $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Status\Manager unit test',
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
      ->method('getCurrentLanguage')
      ->will($this->returnValue($language));

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $namespaces = new ArrayObject();

    $this->paymentStatusManager = new Manager($namespaces, $this->cache, $this->languageManager, $this->moduleHandler);
    $property = new \ReflectionProperty($this->paymentStatusManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentStatusManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentStatusManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentStatusManager, $this->factory);
  }

  /**
   * Tests createInstance().
   */
  public function testCreateInstance() {
    $existing_plugin_id = 'payment_unknown';
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
    $this->paymentStatusManager->createInstance($non_existing_plugin_id);
    $this->paymentStatusManager->createInstance($existing_plugin_id);
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
      ->with('payment_status');
    $this->assertSame($definitions, $this->paymentStatusManager->getDefinitions());
  }

  /**
   * Tests hierarchy().
   *
   * @depends testGetDefinitions
   */
  public function testHierarchy() {
    $parent_label = $this->randomName();
    $child_label = $this->randomName();
    $definitions = array(
      'foo' => array(
        'label' => $parent_label,
      ),
      'bar' => array(
        'label' => $child_label,
        'parent_id' => 'foo',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $expected_hierarchy = array(
      'foo' => array(
        'bar' => array(),
      ),
    );
    $this->assertSame($expected_hierarchy, $this->paymentStatusManager->hierarchy());
  }

  /**
   * Tests options().
   *
   * @depends testGetDefinitions
   * @depends testHierarchy
   */
  public function testOptions() {
    $parent_label = $this->randomName();
    $child_label = $this->randomName();
    $definitions = array(
      'foo' => array(
        'label' => $parent_label,
      ),
      'bar' => array(
        'label' => $child_label,
        'parent_id' => 'foo',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $expected_options = array(
      'foo' => $parent_label,
      'bar' => '- ' . $child_label,
    );
    $this->assertSame($expected_options, $this->paymentStatusManager->options());
  }

  /**
   * Tests getChildren().
   *
   * @depends testGetDefinitions
   */
  public function testGetChildren() {
    $definitions = array(
      'foo' => array(
        'id' => 'foo',
      ),
      'bar' => array(
        'id' => 'bar',
        'parent_id' => 'foo',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->assertSame(array('bar'), $this->paymentStatusManager->getChildren('foo'));
  }

  /**
   * Tests getDescendants().
   *
   * @depends testGetDefinitions
   */
  public function testGetDescendants() {
    $definitions = array(
      'foo' => array(
        'id' => 'foo',
      ),
      'bar' => array(
        'id' => 'bar',
        'parent_id' => 'foo',
      ),
      'baz' => array(
        'id' => 'baz',
        'parent_id' => 'bar',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->assertSame(array('bar', 'baz'), $this->paymentStatusManager->getDescendants('foo'));
  }

  /**
   * Tests getAncestors().
   *
   * @depends testGetDefinitions
   */
  public function testGetAncestors() {
    $definitions = array(
      'foo' => array(
        'id' => 'foo',
      ),
      'bar' => array(
        'id' => 'bar',
        'parent_id' => 'foo',
      ),
      'baz' => array(
        'id' => 'baz',
        'parent_id' => 'bar',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->assertSame(array('bar', 'foo'), $this->paymentStatusManager->getAncestors('baz'));
  }

  /**
   * Tests hasAncestor().
   *
   * @depends testGetDefinitions
   */
  public function testHasAncestor() {
    $definitions = array(
      'foo' => array(
        'id' => 'foo',
      ),
      'bar' => array(
        'id' => 'bar',
        'parent_id' => 'foo',
      ),
      'baz' => array(
        'id' => 'baz',
        'parent_id' => 'bar',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->assertTrue($this->paymentStatusManager->hasAncestor('baz', 'foo'));
    $this->assertFalse($this->paymentStatusManager->hasAncestor('baz', 'baz'));
  }

  /**
   * Tests isOrHasAncestor().
   *
   * @depends testGetDefinitions
   */
  public function testIsOrHasAncestor() {
    $definitions = array(
      'foo' => array(
        'id' => 'foo',
      ),
      'bar' => array(
        'id' => 'bar',
        'parent_id' => 'foo',
      ),
      'baz' => array(
        'id' => 'baz',
        'parent_id' => 'bar',
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $this->assertTrue($this->paymentStatusManager->isOrHasAncestor('baz', 'foo'));
    $this->assertTrue($this->paymentStatusManager->isOrHasAncestor('baz', 'baz'));
  }
}
