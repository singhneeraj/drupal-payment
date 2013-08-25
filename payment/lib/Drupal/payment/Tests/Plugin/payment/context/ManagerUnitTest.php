<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\context\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\context;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\context\Manager.
 */
class ManagerUnitTest extends DrupalUnitTestBase {

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
      'name' => '\Drupal\payment\Plugin\payment\context\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * Tests getDefinitions().
   */
  protected function testGetDefinitions() {
    // Test the default line item plugins.
    $definitions = $this->container->get('plugin.manager.payment.context')->getDefinitions();
    $this->assertEqual(count($definitions), 1);
    foreach ($definitions as $definition) {
      $this->assertIdentical(strpos($definition['id'], 'payment_'), 0);
      $this->assertTrue(is_subclass_of($definition['class'], '\Drupal\payment\Plugin\payment\context\PaymentContextInterface'));
    }
  }

  /**
   * Tests createInstance().
   */
  protected function testCreateInstance() {
    $id = 'payment_unavailable';
    $manager = $this->container->get('plugin.manager.payment.context');
    $this->assertEqual($manager->createInstance($id)->getPluginId(), $id);
    $this->assertEqual($manager->createInstance('ThisIdDoesNotExist')->getPluginId(), $id);
  }
}
