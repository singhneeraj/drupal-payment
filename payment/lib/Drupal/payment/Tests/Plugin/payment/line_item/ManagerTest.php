<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\line_item\ManagerTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\line_item;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\line_item\Manager.
 */
class ManagerTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\payment\line_item\Manager',
      'group' => 'Payment',
    );
  }

  /**
   * Tests getDefinitions().
   */
  function testGetDefinitions() {
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    // Test the default line item plugins.
    $definitions = $manager->getDefinitions();
    $this->assertEqual(count($definitions), 1);
    foreach ($definitions as $definition) {
      $this->assertIdentical(strpos($definition['id'], 'payment_'), 0);
      $this->assertTrue(is_subclass_of($definition['class'], '\Drupal\payment\Plugin\payment\line_item\LineItemInterface'));
    }
  }
}
