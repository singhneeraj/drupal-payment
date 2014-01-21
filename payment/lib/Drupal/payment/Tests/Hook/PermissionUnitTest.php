<?php

/**
 * @file
 * Contains \Drupal\payment\Test\Hook\PermissionUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\payment\Hook\Permission;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Hook\Permission.
 */
class PermissionUnitTest extends UnitTestCase {

  /**
   * The payment method configuration manager.
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
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translationManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Hook\Permission unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    $this->paymentMethodConfigurationManager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');

    $this->translationManager = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->service = new Permission($this->translationManager, $this->paymentMethodConfigurationManager);
  }

  /**
   * @covers \Drupal\payment\Hook\Permission::invoke()
   */
  public function testInvoke() {
    $payment_method_configuration_plugin_id = $this->randomName();
    $payment_method_configuration_label = $this->randomName();
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
    $this->arrayHasKey('payment.payment_method.create.'. $payment_method_configuration_plugin_id, $permissions);
  }
}
