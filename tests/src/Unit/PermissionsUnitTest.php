<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PermissionsUnitTest.
 */

namespace Drupal\Tests\payment\Unit;

use Drupal\payment\Permissions;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Permissions
 *
 * @group Payment
 */
class PermissionsUnitTest extends UnitTestCase {

  /**
   * The payment method configuration manager used for testing.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Permissions.
   */
  protected $service;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->paymentMethodConfigurationManager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->service = new Permissions($this->stringTranslation, $this->paymentMethodConfigurationManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $instance = Permissions::create($container);
    $this->assertInstanceOf('\Drupal\payment\Permissions', $instance);
  }

  /**
   * @covers ::getPermissions
   */
  public function testInvoke() {
    $payment_method_configuration_plugin_id = $this->randomMachineName();
    $payment_method_configuration_label = $this->randomMachineName();
    $payment_method_configuration_definitions = array(
      $payment_method_configuration_plugin_id => array(
        'label' => $payment_method_configuration_label
      ),
    );
    $this->paymentMethodConfigurationManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($payment_method_configuration_definitions));

    $permissions = $this->service->getPermissions();
    $this->assertInternalType('array', $permissions);
    foreach ($permissions as $permission) {
      $this->assertInternalType('array', $permission);
      $this->assertArrayHasKey('title', $permission);
    }
    $this->arrayHasKey('payment.payment_method_configuration.create.'. $payment_method_configuration_plugin_id, $permissions);
  }
}
