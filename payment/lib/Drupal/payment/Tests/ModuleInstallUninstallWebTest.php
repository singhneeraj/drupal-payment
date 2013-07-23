<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\ModuleInstallUninstallWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Plugin\Core\Entity\PaymentMethodInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests module installation and uninstallation.
 */
class ModuleInstallUninstallWebTest extends WebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'Module installation and uninstallation',
      'group' => 'Payment',
    );
  }

  /**
   * Test installation and uninstallation.
   */
  function testInstallationAndUninstallation() {
    $this->assertTrue(module_exists('payment'));

    // Test default configuration.
    $names = array('collect_on_delivery', 'no_payment_required');
    foreach ($names as $name) {
      $payment_method = entity_load('payment_method', $name);
      $this->assertTrue($payment_method instanceof PaymentMethodInterface);
    }

    module_disable(array('payment'));
    module_uninstall(array('payment'));
    $this->assertFalse(module_exists('payment'));
  }
}
