<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\status\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\status;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Manager.
 */
class ManagerUnitTest extends DrupalUnitTestBase {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\payment\status\Manager
   */
  public $manager;

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
      'name' => '\Drupal\payment\Plugin\payment\status\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.payment.status');
  }

  /**
   * Tests getDefinitions().
   */
  protected function testGetDefinitions() {
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
  protected function testCreateInstance() {
    $id = 'payment_unknown';
    $this->assertEqual($this->manager->createInstance($id)->getPluginId(), $id);
    $this->assertEqual($this->manager->createInstance('ThisIdDoesNotExist')->getPluginId(), $id);
  }

  /**
   * Tests options().
   */
  protected function testOptions() {
    $this->assertTrue($this->manager->options());
  }
}
