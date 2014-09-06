<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Hook\PermissionUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Hook;

use Drupal\payment\Hook\Permission;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\Permission
 *
 * @group Payment
 */
class PermissionUnitTest extends UnitTestCase {

  /**
   * The payment method configuration manager used for testing.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\Permission.
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

    $this->service = new Permission($this->stringTranslation, $this->paymentMethodConfigurationManager);
  }

  /**
   * @covers ::invoke
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

    $permissions = $this->service->invoke();
    $this->assertInternalType('array', $permissions);
    foreach ($permissions as $permission) {
      $this->assertInternalType('array', $permission);
      $this->assertArrayHasKey('title', $permission);
    }
    $this->arrayHasKey('payment.payment_method_configuration.create.'. $payment_method_configuration_plugin_id, $permissions);
  }
}
