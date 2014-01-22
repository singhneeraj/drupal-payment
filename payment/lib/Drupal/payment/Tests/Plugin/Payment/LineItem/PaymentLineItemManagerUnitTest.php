<?php

/**
 * @file Contains \Drupal\payment\Tests\Plugin\Payment\LineItem\PaymentLineItemManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\LineItem;

use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManager;
use Drupal\Tests\UnitTestCase;
use Zend\Stdlib\ArrayObject;

/**
 * Tests \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface.
 */
class PaymentLineItemManagerUnitTest extends UnitTestCase {

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
   * @var \Drupal\Component\Plugin\Factory\DefaultFactory|\PHPUnit_Framework_MockObject_MockObject
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
   * The payment line_item plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   */
  public $paymentLineItemManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->discovery = $this->getMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');

    $this->factory = $this->getMockBuilder('\Drupal\Component\Plugin\Factory\DefaultFactory')
      ->disableOriginalConstructor()
      ->getMock();

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

    $this->paymentLineItemManager = new PaymentLineItemManager($namespaces, $this->cache, $this->languageManager, $this->moduleHandler);
    $discovery_property = new \ReflectionProperty($this->paymentLineItemManager, 'discovery');
    $discovery_property->setAccessible(TRUE);
    $discovery_property->setValue($this->paymentLineItemManager, $this->discovery);
    $factory_property = new \ReflectionProperty($this->paymentLineItemManager, 'factory');
    $factory_property->setAccessible(TRUE);
    $factory_property->setValue($this->paymentLineItemManager, $this->factory);
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
      ->with('payment_line_item');
    $this->assertSame($definitions, $this->paymentLineItemManager->getDefinitions());
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
    $this->assertSame($expected_options, $this->paymentLineItemManager->options());
  }
}
