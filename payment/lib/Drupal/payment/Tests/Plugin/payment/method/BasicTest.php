<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\method\BaseTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 */
class BasicTest extends DrupalUnitTestBase {

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
    $this->method = \Drupal::service('plugin.manager.payment.payment_method')->createInstance('payment_basic');
  }

  /**
   * Tests setAmount() and getAmount().
   */
  function testGetConfiguration() {
    $this->method->setMessageText('foo');
    $this->method->setMessageTextFormat('bar');
    $this->method->setStatus('baz');
    $this->assertEqual($this->method->getConfiguration(), array(
      'messageText' => 'foo',
      'messageTextFormat' => 'bar',
      'status' => 'baz',
    ));
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
   * Tests paymentMethodFormElements().
   */
  function testPaymentMethodFormElements() {
    $form = array();
    $form_state = array();
    $this->assertTrue(is_array($this->method->paymentMethodFormElements($form, $form_state)));
  }
}
