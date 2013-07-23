<?php

/**
 * @file
 * Contains class Drupal\payment\Tests\UpgradePathWithContentWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\system\Tests\Upgrade\UpgradePathTestBase;

/**
 * Tests Payment's upgrade path.
 */
class UpgradePathWithoutContentWebTest extends UpgradePathTestBase {

  static function getInfo() {
    return array(
      'name'  => 'Upgrade path (without existing content and configuration)',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp() {
    $this->databaseDumpFiles[] = drupal_get_path('module', 'payment') . '/../payment-database-dump.php';
    parent::setUp();
  }

  /**
   * Tests a successful upgrade.
   */
  function testPaymentUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');
  }
}
