<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\method\BaseTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;
use Drupal\simpletest\WebtestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 */
class BasicTest extends WebtestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\payment\method\Basic',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->methodEntity = entity_create('payment_method', array());
    $this->method = \Drupal::service('plugin.manager.payment.payment_method')->createInstance('payment_basic');
    $this->method->setPaymentMethod($this->methodEntity);
  }

  /**
   * Tests setAmount() and getAmount().
   */
  function testGetConfiguration() {
    $this->method->setMessageText('foo')
      ->setMessageTextFormat('bar')
      ->setStatus('baz')
      ->setBrandOption('Foo');
    $this->assertEqual($this->method->getConfiguration(), array(
      'brandOption' => 'Foo',
      'messageText' => 'foo',
      'messageTextFormat' => 'bar',
      'status' => 'baz',
    ));
  }

  /**
   * Tests setPaymentMethod() and getPaymentMethod().
   */
  function testGetPaymentMethod() {
    $payment_method = entity_create('payment_method', array());
    $this->assertTrue($this->method->setPaymentMethod($payment_method) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getPaymentMethod(), $payment_method);
  }

  /**
   * Tests setMessageText() and getMessageText().
   */
  function testGetMessageText() {
    $text = $this->randomName();
    $this->assertTrue($this->method->setMessageText($text) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getMessageText(), $text);
  }

  /**
   * Tests setMessageTextFormat() and getMessageTextFormat().
   */
  function testGetMessageTextFormat() {
    $format = $this->randomName();
    $this->assertTrue($this->method->setMessageTextFormat($format) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getMessageTextFormat(), $format);
  }

  /**
   * Tests setStatus() and getStatus().
   */
  function testGetStatus() {
    $status = $this->randomName();
    $this->assertTrue($this->method->setStatus($status) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->getStatus(), $status);
  }

  /**
   * Tests setBrandOption() and brandOptions().
   */
  function testBrandOptions() {
    $this->assertIdentical($this->method->brandOptions(), array(
      'default' => $this->methodEntity->label(),
    ));
    $label = $this->randomName();
    $this->assertTrue($this->method->setbrandOption($label) instanceof PaymentMethodInterface);
    $this->assertIdentical($this->method->brandOptions(), array(
      'default' => $label,
    ));
  }

  /**
   * Tests paymentFormElements().
   */
  function testPaymentFormElements() {
    $this->method->setMessageText('Hello [site:name]!');
    $form = array();
    $form_state = array(
      'payment' => entity_create('payment', array()),
    );
    $elements = $this->method->paymentFormElements($form, $form_state);
    $this->assertIdentical($elements['message']['#markup'], "<p>Hello Drupal!</p>\n");
    $this->assertTrue(is_array($this->method->paymentFormElements($form, $form_state)));
  }

  /**
   * Tests paymentMethodFormElements().
   */
  function testPaymentMethodFormElements() {
    $form = array();
    $form_state = array();
    $this->assertTrue(is_array($this->method->paymentMethodFormElements($form, $form_state)));
  }

  /**
   * Tests paymentOperationAccess().
   */
  function testPaymentOperationAccess() {
    $payment = entity_create('payment', array());
    $this->assertTrue($this->method->paymentOperationAccess($payment, 'execute', 'default'));
    $this->assertFalse($this->method->paymentOperationAccess($payment, $this->randomName(), 'default'));
  }

  /**
   * Tests executePaymentOperation().
   */
  function testExecutePaymentOperation() {
    $plugin_id = 'payment_unknown';
    $payment = entity_create('payment', array());
    $this->method->setStatus($plugin_id);
    $this->method->executePaymentOperation($payment, 'execute', 'default');
    $this->assertTrue($payment->getStatus()->getPluginId() == $plugin_id);
  }
}
