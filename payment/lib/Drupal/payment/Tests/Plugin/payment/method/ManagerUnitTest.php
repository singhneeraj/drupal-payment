<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\method\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\method\Manager.
 */
class ManagerUnitTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\method\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->manager = \Drupal::service('plugin.manager.payment.payment_method');
  }

  /**
   * Tests getDefinitions().
   */
  function testGetDefinitions() {
    // Test the default payment method plugins.
    $definitions = $this->manager->getDefinitions();
    $this->assertEqual(count($definitions), 2);
    foreach ($definitions as $definition) {
      $this->assertIdentical(strpos($definition['id'], 'payment_'), 0);
      $this->assertTrue(is_subclass_of($definition['class'], '\Drupal\payment\Plugin\payment\method\PaymentMethodInterface'));
    }
  }

  /**
   * Tests createInstance().
   */
  function testCreateInstance() {
    $id = 'payment_unavailable';
    $this->assertEqual($this->manager->createInstance($id)->getPluginId(), $id);
    $this->assertEqual($this->manager->createInstance('ThisIdDoesNotExist')->getPluginId(), $id);
  }
}
