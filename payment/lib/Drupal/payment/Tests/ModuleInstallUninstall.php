<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\ModuleInstallUninstall.
 */

namespace Drupal\payment\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests module installation and uninstallation.
 */
class ModuleInstallUninstall extends WebTestBase {

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
   * Test uninstall.
   */
  function testUninstallation() {
    $this->assertTrue(module_exists('payment'));
    module_disable(array('payment'));
    module_uninstall(array('payment'));
    $this->assertFalse(module_exists('payment'));
  }
}
