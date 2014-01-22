<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Entity\PaymentMethodUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Payment;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Entity\PaymentMethod.
 */
class PaymentMethodUnitTest extends DrupalUnitTestBase {

  /**
   * The payment method plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
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
    $this->manager = Payment::methodManager();
    $this->paymentMethod = entity_create('payment_method', array(
      'pluginId' => 'payment_unavailable'
    ));
  }

  /**
   * Tests bundle().
   */
  protected function testBundle() {
    $this->assertIdentical($this->paymentMethod->bundle(), 'payment_unavailable');
  }

  /**
   * Tests getPluginId().
   */
  protected function testPluginId() {
    $this->assertIdentical($this->paymentMethod->getPluginId(), 'payment_unavailable');
  }

  /**
   * Tests setConfiguration() and getConfiguration().
   */
  protected function testGetConfiguration() {
    $configuration = array($this->randomName());
    $this->assertEqual(spl_object_hash($this->paymentMethod->setPluginConfiguration($configuration)), spl_object_hash($this->paymentMethod));
    $this->assertEqual($this->paymentMethod->getPluginConfiguration(), $configuration);
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
}
