<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Status\PaymentStatusManagerUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Status;

use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManager;
use Drupal\Tests\UnitTestCase;

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
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->classResolver = $this->getMock('\Drupal\Core\DependencyInjection\ClassResolverInterface');

    $this->discovery = $this->getMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');

    $this->factory = $this->getMock('\Drupal\Component\Plugin\Factory\FactoryInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $this->moduleHandler->expects($this->atLeastOnce())
      ->method('getModuleDirectories')
      ->willReturn([]);

    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->paymentStatusManager = new PaymentStatusManager($this->cache, $this->moduleHandler, $this->classResolver, $this->stringTranslation);
    $property = new \ReflectionProperty($this->paymentStatusManager, 'discovery');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentStatusManager, $this->discovery);
    $property = new \ReflectionProperty($this->paymentStatusManager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->paymentStatusManager, $this->factory);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->paymentStatusManager = new PaymentStatusManager($this->cache, $this->moduleHandler, $this->classResolver, $this->stringTranslation);
  }

  /**
   * @covers ::getFallbackPluginId
   */
  public function testGetFallbackPluginId() {
    $plugin_id = $this->randomMachineName();
    $plugin_configuration = array($this->randomMachineName());
    $this->assertInternalType('string', $this->paymentStatusManager->getFallbackPluginId($plugin_id, $plugin_configuration));
  }

  /**
   * @covers ::getDefinitions
   * @covers ::processDefinition
   */
  public function testGetDefinitions() {
    $discovery_definitions = array(
      'foo' => array(
        'id' => NULL,
        'parent_id' => NULL,
        'label' => $this->randomMachineName(),
        'description' => NULL,
        'operations_provider' => NULL,
        'class' => 'Drupal\payment\Plugin\Payment\Status\DefaultPaymentStatus',
      ),
    );
    $manager_definitions = $discovery_definitions;
    $manager_definitions['foo']['label'] = (new TranslationWrapper($manager_definitions['foo']['label']))->setStringTranslation($this->stringTranslation);
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($discovery_definitions));
    $this->moduleHandler->expects($this->once())
      ->method('alter')
      ->with('payment_status');
    $this->assertEquals($manager_definitions, $this->paymentStatusManager->getDefinitions());
  }

  /**
   * @covers ::hierarchy
   * @covers ::hierarchyLevel
   * @covers ::sort
   * @depends testGetDefinitions
   */
  public function testHierarchy() {
    $parent_label = $this->randomMachineName();
    $child_label = $this->randomMachineName();
    $definitions = array(
      'foo' => array(
        'label' => $parent_label,
      ),
      'bar' => array(
        'label' => $child_label,
        'parent_id' => 'foo',
      ),
      'baz' => array(
        'label' => $this->randomMachineName(),
      ),
    );
    $this->discovery->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $expected_hierarchy = array(
      'foo' => array(
        'bar' => [],
      ),
    );
    $this->assertSame($expected_hierarchy, $this->paymentStatusManager->hierarchy(array('foo', 'bar')));
  }

  /**
   * @covers ::options
   * @covers ::optionsLevel
   * @depends testGetDefinitions
   * @depends testHierarchy
   */
  public function testOptions() {
    $parent_label = $this->randomMachineName();
    $child_label = $this->randomMachineName();
    $definitions = array(
      'foo' => array(
        'label' => $parent_label,
      ),
      'bar' => array(
        'label' => $child_label,
        'parent_id' => 'foo',
      ),
      'baz' => array(
        'label' => $this->randomMachineName(),
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
