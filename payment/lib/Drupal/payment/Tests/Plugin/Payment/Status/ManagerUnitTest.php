<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\Status\ManagerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Status;

use Drupal\payment\Payment;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\Payment\Status\Manager.
 */
class ManagerUnitTest extends DrupalUnitTestBase {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
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
      'name' => '\Drupal\payment\Plugin\Payment\Status\Manager unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->manager = Payment::statusManager();
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
      $this->assertTrue(is_subclass_of($definition['class'], '\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface'));
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

  /**
   * Tests getChildren().
   */
  protected function testGetChildren() {
    $expected = array('payment_created', 'payment_failed', 'payment_pending');
    $this->assertEqual($this->manager->getChildren('payment_no_money_transferred'), $expected);
  }

  /**
   * Tests getDescendants().
   */
  protected function testGetDescendants() {
    $expected = array('payment_created', 'payment_failed', 'payment_pending', 'payment_authorization_failed', 'payment_cancelled', 'payment_expired');
    $this->assertEqual($this->manager->getDescendants('payment_no_money_transferred'), $expected);
  }

  /**
   * Tests getAncestors().
   */
  protected function testGetAncestors() {
    $expected = array('payment_failed', 'payment_no_money_transferred');
    $this->assertEqual($this->manager->getAncestors('payment_authorization_failed'), $expected);
  }

  /**
   * Tests hasAncestor().
   */
  protected function testHasAncestor() {
    $this->assertTrue($this->manager->isOrHasAncestor('payment_failed', 'payment_no_money_transferred'));
  }

  /**
   * Tests isOrHasAncestor().
   */
  protected function testIsOrHasAncestor() {
    $this->assertTrue($this->manager->isOrHasAncestor('payment_failed', 'payment_failed'));
    $this->assertTrue($this->manager->isOrHasAncestor('payment_failed', 'payment_no_money_transferred'));
  }
}
