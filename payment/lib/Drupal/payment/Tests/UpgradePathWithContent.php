<?php

/**
 * @file
 * Contains class Drupal\payment\Tests\UpgradePathWithContent.
 */

namespace Drupal\payment\Tests;

use Drupal\system\Tests\Upgrade\UpgradePathTestBase;

/**
 * Tests Payment's upgrade path.
 */
class UpgradePathWithContent extends UpgradePathTestBase {

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
    foreach (array(1, 2) as $id) {
      $payment = entity_load('payment', $id);
      $this->assertTrue((bool) $payment);
      $this->assertEqual(count($payment->getLineItems()), 2);
      $this->assertEqual(count($payment->getStatuses()), 1);
      $this->assertTrue(is_string($payment->getPaymentMethodId()));
      $this->assertTrue(is_numeric($payment->getOwnerId()));
    }
  }
}
