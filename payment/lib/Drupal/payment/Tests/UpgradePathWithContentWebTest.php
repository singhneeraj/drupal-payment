<?php

/**
 * @file
 * Contains class Drupal\payment\Tests\UpgradePathWithContentWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\system\Tests\Upgrade\UpgradePathTestBase;
use Drupal\payment\Plugin\payment\method\PaymentMethodInterface as PluginPaymentMethodInterface;

/**
 * Tests Payment's upgrade path.
 */
class UpgradePathWithContentWebTest extends UpgradePathTestBase {

  static function getInfo() {
    return array(
      'name'  => 'Upgrade path (with existing content and configuration)',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp() {
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'payment') . '/../payment-database-dump.php',
      drupal_get_path('module', 'payment') . '/../payment-database-dump-content.php',
    );
    parent::setUp();
  }

  /**
   * Tests a successful upgrade.
   */
  function testPaymentUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // Test payment integrity.
    $ids = array(1, 2);
    foreach ($ids as $id) {
      $payment = entity_load('payment', $id);
      $this->assertTrue((bool) $payment);
      $this->assertEqual(count($payment->getLineItems()), 2);
      $this->assertEqual(count($payment->getStatuses()), 1);
      $this->assertTrue(is_string($payment->getPaymentMethodId()));
      $this->assertTrue(is_numeric($payment->getOwnerId()));
    }

    // Test payment method integrity.
    $names = array('gakdcnxd', 'eh9wkb2p');
    foreach ($names as $name) {
      $payment_method = entity_load('payment_method', $name);
      $this->assertTrue((bool) $payment_method);
      $this->assertTrue(is_string($payment_method->id()));
      $this->assertTrue(is_string($payment_method->label()));
      $this->assertTrue(is_int($payment_method->getOwnerId()));
      $this->assertTrue($payment_method->getPlugin() instanceof PluginPaymentMethodInterface);
      if ($payment_method->getPlugin()->getPluginId() == 'payment_basic') {
        $brand_options = $payment_method->brandOptions();
        $this->assertTrue(strlen(reset($brand_options)));
      }
    }
  }
}
