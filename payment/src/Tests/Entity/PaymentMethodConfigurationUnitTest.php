<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentMethodConfigurationUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Payment;
use Drupal\simpletest\KernelTestBase;

/**
 * \Drupal\payment\Entity\PaymentMethodConfiguration unit test.
 *
 * @group Payment
 */
class PaymentMethodConfigurationUnitTest extends KernelTestBase {

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
   * @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface
   */
  protected $paymentMethodConfiguration;

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->manager = Payment::methodManager();
    $this->paymentMethodConfiguration = entity_create('payment_method_configuration', array(
      'pluginId' => 'payment_unavailable'
    ));
  }

  /**
   * Tests bundle().
   */
  protected function testBundle() {
    $this->assertIdentical($this->paymentMethodConfiguration->bundle(), 'payment_unavailable');
  }

  /**
   * Tests getPluginId().
   */
  protected function testPluginId() {
    $this->assertIdentical($this->paymentMethodConfiguration->getPluginId(), 'payment_unavailable');
  }

  /**
   * Tests setConfiguration() and getConfiguration().
   */
  protected function testGetConfiguration() {
    $configuration = array($this->randomName());
    $this->assertEqual(spl_object_hash($this->paymentMethodConfiguration->setPluginConfiguration($configuration)), spl_object_hash($this->paymentMethodConfiguration));
    $this->assertEqual($this->paymentMethodConfiguration->getPluginConfiguration(), $configuration);
  }

  /**
   * Tests setLabel() and label().
   */
  protected function testLabel() {
    $label = $this->randomName();
    $this->assertTrue($this->paymentMethodConfiguration->setLabel($label) instanceof PaymentMethodConfigurationInterface);
    $this->assertIdentical($this->paymentMethodConfiguration->label(), $label);
  }

  /**
   * Tests setOwnerId() and getOwnerId().
   */
  protected function testGetOwnerId() {
    $id = 9;
    $this->assertTrue($this->paymentMethodConfiguration->setOwnerId($id) instanceof PaymentMethodConfigurationInterface);
    $this->assertIdentical($this->paymentMethodConfiguration->getOwnerId(), $id);
  }

  /**
   * Tests setId() and id().
   */
  protected function testId() {
    $id = 26;
    $this->assertTrue($this->paymentMethodConfiguration->setId($id) instanceof PaymentMethodConfigurationInterface);
    $this->assertIdentical($this->paymentMethodConfiguration->id(), $id);
  }

  /**
   * Tests setUuid() and uuid().
   */
  protected function testUuid() {
    $uuid = 'foo';
    $this->assertTrue($this->paymentMethodConfiguration->setUuid($uuid) instanceof PaymentMethodConfigurationInterface);
    $this->assertIdentical($this->paymentMethodConfiguration->uuid(), $uuid);
  }
}
