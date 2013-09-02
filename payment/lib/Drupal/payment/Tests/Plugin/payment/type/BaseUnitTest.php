<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\payment\type\BaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\type;

use Drupal\payment\Plugin\payment\type\PaymentTypeInterface ;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 */
class BaseUnitTest extends DrupalUnitTestBase {

  /**
   * The payment type to test.
   *
   * @var \Drupal\payment\Plugin\payment\type\Base
   */
  protected $type;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field', 'payment', 'payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\type\Base unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->type = $this->container->get('plugin.manager.payment.type')->createInstance('payment_test', array());
    $storage_controller = $this->container->get('plugin.manager.entity')->getStorageController('payment');
    $this->type->setPayment($storage_controller->create(array(
      'type' => $this->type,
    )));
  }

  /**
   * Tests resume().
   */
  protected function testResume() {
    $this->type->resumeContext();
    $state = $this->container->get('state');
    $this->assertEqual($state->get('payment_test_payment_type_pre_resume_context'), TRUE);
  }

  /**
   * Tests paymentDescription().
   */
  protected function testPaymentDescription() {
    $this->assertEqual($this->type->paymentDescription(), 'The commander promoted Dirkjan to Major Failure.');

  }

  /**
   * Tests setPayment() and getPayment().
   */
  protected function testGetPayment() {
    $payment = entity_create('payment', array(
      'type' => $this->type,
    ));
    $this->assertTrue($this->type->setPayment($payment) instanceof PaymentTypeInterface );
    $this->assertTrue($this->type->getPayment() === $payment);
  }
}
