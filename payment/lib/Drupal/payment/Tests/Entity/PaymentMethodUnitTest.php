<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Entity\PaymentMethodUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Entity\PaymentMethod.
 */
class PaymentMethodUnitTest extends DrupalUnitTestBase {

  /**
   * The payment method plugin manager.
   *
   * @var \Drupal\payment\Plugin\payment\method\Manager
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'system');

  /**
   * The payment method to test on.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $paymentMethod;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Entity\PaymentMethod unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.payment.method');
    $this->paymentMethod = entity_create('payment_method', array());
  }

  /**
   * Tests setLabel() and label().
   */
  protected function testLabel() {
    $label = $this->randomName();
    $this->assertTrue($this->paymentMethod->setLabel($label) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->label(), $label);
  }

  /**
   * Tests setPlugin() and getPlugin().
   */
  protected function testGetPlugin() {
    $plugin = $this->manager->createInstance('payment_unavailable');
    $this->assertTrue($this->paymentMethod->setPlugin($plugin) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->getPlugin(), $plugin);
  }

  /**
   * Tests setOwnerId() and getOwnerId().
   */
  protected function testGetOwnerId() {
    $id = 9;
    $this->assertTrue($this->paymentMethod->setOwnerId($id) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->getOwnerId(), $id);
  }

  /**
   * Tests setId() and id().
   */
  protected function testId() {
    $id = 26;
    $this->assertTrue($this->paymentMethod->setId($id) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->id(), $id);
  }

  /**
   * Tests setUuid() and uuid().
   */
  protected function testUuid() {
    $uuid = 'foo';
    $this->assertTrue($this->paymentMethod->setUuid($uuid) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->paymentMethod->uuid(), $uuid);
  }

  /**
   * Tests currencies().
   */
  protected function testCurrencies() {
    $this->paymentMethod->setPlugin($this->manager->createInstance('payment_unavailable'));
    $this->assertTrue(is_array($this->paymentMethod->currencies()));
  }

  /**
   * Tests paymentFormElements().
   */
  protected function testPaymentFormElements() {
    $this->paymentMethod->setPlugin($this->manager->createInstance('payment_unavailable'));
    $this->assertTrue(is_array($this->paymentMethod->currencies()));
  }
}
