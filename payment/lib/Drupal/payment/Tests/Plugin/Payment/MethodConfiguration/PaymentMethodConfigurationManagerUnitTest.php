<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodConfiguration;

use Drupal\Component\Plugin\Exception\PluginException;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManager.
 */
class PaymentMethodConfigurationManagerUnitTest extends UnitTestCase {

  /**
   * The plugin factory used for testing.
   *
   * @var \Drupal\Component\Plugin\Factory\FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $factory;

  /**
   * The plugin manager under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    $this->factory = $this->getMock('\Drupal\Component\Plugin\Factory\FactoryInterface');

    $this->manager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManager')
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
    $property = new \ReflectionProperty($this->manager, 'factory');
    $property->setAccessible(TRUE);
    $property->setValue($this->manager, $this->factory);
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
    $this->manager->createInstance($non_existing_plugin_id);
    $this->manager->createInstance($existing_plugin_id);
  }
}
