<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Core\Entity\PaymentMethodUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\Entity;

use Drupal\payment\Plugin\Core\Entity\PaymentMethodInterface;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\Core\Entity\PaymentMethod.
 */
class PaymentMethodUnitTest extends DrupalUnitTestBase {

  public static $modules = array('payment', 'system');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Core\Entity\PaymentMethod unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->manager = \Drupal::service('plugin.manager.payment.payment_method');
    $this->paymentMethod = entity_create('payment_method', array());
  }

  /**
   * Tests setLabel() and label().
   */
  function testLabel() {
    $label = $this->randomName();
    $this->assertTrue($this->paymentMethod->setLabel($label) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->label(), $label);
  }

  /**
   * Tests setPlugin() and getPlugin().
   */
  function testGetPlugin() {
    $plugin = $this->manager->createInstance('payment_unavailable');
    $this->assertTrue($this->paymentMethod->setPlugin($plugin) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->getPlugin(), $plugin);
  }

  /**
   * Tests setOwnerId() and getOwnerId().
   */
  function testGetOwnerId() {
    $id = 9;
    $this->assertTrue($this->paymentMethod->setOwnerId($id) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->getOwnerId(), $id);
  }

  /**
   * Tests setId() and id().
   */
  function testId() {
    $id = 26;
    $this->assertTrue($this->paymentMethod->setId($id) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->id(), $id);
  }

  /**
   * Tests setUuid() and uuid().
   */
  function testUuid() {
    $uuid = 'foo';
    $this->assertTrue($this->paymentMethod->setUuid($uuid) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->uuid(), $uuid);
  }

  /**
   * Tests currencies().
   */
  function testCurrencies() {
    $this->paymentMethod->setPlugin($this->manager->createInstance('payment_unavailable'));
    $this->assertTrue(is_array($this->paymentMethod->currencies()));
  }

  /**
   * Tests paymentFormElements().
   */
  function testPaymentFormElements() {
    $this->paymentMethod->setPlugin($this->manager->createInstance('payment_unavailable'));
    $this->assertTrue(is_array($this->paymentMethod->currencies()));
  }
}
