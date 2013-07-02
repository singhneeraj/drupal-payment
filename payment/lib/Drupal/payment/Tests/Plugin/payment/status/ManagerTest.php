<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\status\ManagerTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\status;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Manager.
 */
class ManagerTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\payment\status\Manager',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->manager = \Drupal::service('plugin.manager.payment.status');
  }

  /**
   * Tests getDefinitions().
   */
  function testGetDefinitions() {
    // Test the default status plugins.
    $definitions = $this->manager->getDefinitions();
    $this->assertEqual(count($definitions), 10);
    foreach ($definitions as $definition) {
      $this->assertIdentical(strpos($definition['id'], 'payment_'), 0);
      $this->assertTrue(is_subclass_of($definition['class'], '\Drupal\payment\Plugin\payment\status\PaymentStatusInterface'));
    }
  }

  /**
   * Tests createInstance().
   */
  function testCreateInstance() {
    $id = 'payment_unknown';
    $this->assertEqual($this->manager->createInstance($id)->getPluginId(), $id);
    $this->assertEqual($this->manager->createInstance('ThisIdDoesNotExist')->getPluginId(), $id);
  }
}
