<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\method\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\method\Basic.
 */
class BasicUnitTest extends DrupalUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field', 'payment');

  /**
   * The payment method plugin.
   *
   * @var \Drupal\payment\Plugin\payment\method\Basic
   */
  protected $method;

  /**
   * The payment method entity.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $methodEntity;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\method\Basic unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->methodEntity = entity_create('payment_method', array());
    $this->method = \Drupal::service('plugin.manager.payment.method')->createInstance('payment_basic');
    $this->method->setPaymentMethod($this->methodEntity);
  }

  /**
   * Tests setAmount() and getAmount().
   */
  protected function testGetConfiguration() {
    $this->method->setMessageText('foo')
      ->setMessageTextFormat('bar')
      ->setStatus('baz')
      ->setBrandLabel('Foo');
    $this->assertEqual($this->method->getConfiguration(), array(
      'brand_option' => 'Foo',
      'message_text' => 'foo',
      'message_text_format' => 'bar',
      'status' => 'baz',
    ));
  }

  /**
   * Tests setPaymentMethod() and getPaymentMethod().
   */
  protected function testGetPaymentMethod() {
    $payment_method = entity_create('payment_method', array());
    $this->assertTrue($this->method->setPaymentMethod($payment_method) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getPaymentMethod(), $payment_method);
  }

  /**
   * Tests setMessageText() and getMessageText().
   */
  protected function testGetMessageText() {
    $text = $this->randomName();
    $this->assertTrue($this->method->setMessageText($text) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getMessageText(), $text);
  }

  /**
   * Tests setMessageTextFormat() and getMessageTextFormat().
   */
  protected function testGetMessageTextFormat() {
    $format = $this->randomName();
    $this->assertTrue($this->method->setMessageTextFormat($format) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getMessageTextFormat(), $format);
  }

  /**
   * Tests setStatus() and getStatus().
   */
  protected function testGetStatus() {
    $status = $this->randomName();
    $this->assertTrue($this->method->setStatus($status) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getStatus(), $status);
  }

  /**
   * Tests setBrandLabel() and brands().
   */
  protected function testBrands() {
    $brands = $this->method->brands();
    $this->assertIdentical($brands['default']['label'], $this->methodEntity->label());
    $label = $this->randomName();
    $this->assertTrue($this->method->setBrandLabel($label) instanceof PaymentMethodInterface);
    $brands = $this->method->brands();
    $this->assertIdentical($brands['default']['label'], $label);
  }

  /**
   * Tests paymentMethodFormElements().
   */
  protected function testPaymentMethodFormElements() {
    $form = array();
    $form_state = array();
    $this->assertTrue(is_array($this->method->paymentMethodFormElements($form, $form_state)));
  }

  /**
   * Tests executePaymentAccess().
   */
  protected function testExecutePaymentAccess() {
    $payment = entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    ));
    $this->assertTrue($this->method->executePaymentAccess($payment, 'default'));
  }
}
