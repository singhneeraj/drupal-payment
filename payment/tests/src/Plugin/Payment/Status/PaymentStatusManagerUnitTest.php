<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Status\PaymentStatusManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Status;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManager;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Stdlib\ArrayObject;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Status\PaymentStatusManager
 *
 * @group Payment
 */
class PaymentStatusManagerUnitTest extends UnitTestCase {

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
   * The payment status plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManager
   */
  public $paymentStatusManager;

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

    $this->paymentStatusManager = new PaymentStatusManager($namespaces, $this->cache, $this->moduleHandler, $this->classResolver);
    $property = new \ReflectionProperty($this->paymentStatusManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentStatusManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentStatusManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentStatusManager, $this->factory);
  }

  /**
   * @covers ::createInstance
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
      ->with('payment_status');
    $this->assertSame($definitions, $this->paymentStatusManager->getDefinitions());
  }

  /**
   * @covers ::hierarchy
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
      'baz' => array(
        'label' => $this->randomName(),
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
    $this->assertSame($expected_hierarchy, $this->paymentStatusManager->hierarchy(array('foo', 'bar')));
  }

  /**
   * @covers ::options
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
      'baz' => array(
        'label' => $this->randomName(),
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $expected_options = array(
      'foo' => $parent_label,
      'bar' => '- ' . $child_label,
    );
    $this->assertSame($expected_options, $this->paymentStatusManager->options(array('foo', 'bar')));
  }

  /**
   * @covers ::getChildren
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
   * @covers ::getDescendants
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
   * @covers ::getAncestors
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
   * @covers ::hasAncestor
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
   * @covers ::isOrHasAncestor
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
