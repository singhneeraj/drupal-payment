<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\UpgradeMapUnitTest.
 */

namespace Drupal\payment\Tests;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests the payment_upgrade_map_*() functions.
 */
class UpgradeMapUnitTest extends DrupalUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'Upgrade maps',
      'group' => 'Payment',
    );
  }

  /**
   * Tests payment_upgrade_8x2x_map_status().
   */
  protected function testStatus() {
    module_load_install('payment');
    $manager = $this->container->get('plugin.manager.payment.status');
    $pluginIds = array_keys($manager->getDefinitions());
    $this->assertFalse(array_diff(payment_upgrade_8x2x_map_status(), $pluginIds));
  }

  /**
   * Tests payment_upgrade_8x2x_map_payment_method().
   */
  protected function testPaymentMethod() {
    module_load_install('payment');
    $manager = $this->container->get('plugin.manager.payment.payment_method');
    $pluginIds = array_keys($manager->getDefinitions());
    $this->assertFalse(array_diff(payment_upgrade_8x2x_map_payment_method(), $pluginIds));
  }
}
