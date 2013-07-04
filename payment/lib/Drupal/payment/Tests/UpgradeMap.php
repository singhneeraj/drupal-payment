<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\UpgradeMap.
 */

namespace Drupal\payment\Tests;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests the payment_upgrade_map_*() functions.
 */
class UpgradeMap extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'Upgrade maps',
      'group' => 'Payment',
    );
  }

  /**
   * Tests payment_upgrade_8x2x_map_status().
   */
  function testStatus() {
    module_load_install('payment');
    $manager = \Drupal::service('plugin.manager.payment.status');
    $pluginIds = array_keys($manager->getDefinitions());
    $this->assertFalse(array_diff(payment_upgrade_8x2x_map_status(), $pluginIds));
  }

  /**
   * Tests payment_upgrade_8x2x_map_payment_method().
   */
  function testPaymentMethod() {
    module_load_install('payment');
    $manager = \Drupal::service('plugin.manager.payment.payment_method');
    $pluginIds = array_keys($manager->getDefinitions());
    $this->assertFalse(array_diff(payment_upgrade_8x2x_map_payment_method(), $pluginIds));
  }
}
