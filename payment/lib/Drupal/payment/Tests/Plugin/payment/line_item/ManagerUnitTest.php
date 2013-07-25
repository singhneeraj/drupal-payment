<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\line_item\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\line_item;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\line_item\Manager.
 */
class ManagerUnitTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\line_item\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->manager = \Drupal::service('plugin.manager.payment.line_item');
  }

  /**
   * Tests getDefinitions().
   */
  function testGetDefinitions() {
    // Test the default line item plugins.
    $definitions = $this->manager->getDefinitions();
    $this->assertEqual(count($definitions), 1);
    foreach ($definitions as $definition) {
      $this->assertIdentical(strpos($definition['id'], 'payment_'), 0);
      $this->assertTrue(is_subclass_of($definition['class'], '\Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface'));
    }
  }

  /**
   * Tests createInstance().
   */
  function testCreateInstance() {
    $id = 'payment_basic';
    $this->assertEqual($this->manager->createInstance($id)->getPluginId(), $id);
    $this->assertEqual($this->manager->createInstance('ThisIdDoesNotExist')->getPluginId(), $id);
  }
}
