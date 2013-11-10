<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentPaymentMethodInputWebTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment_payment_method_input element.
 */
class PaymentPaymentMethodInputWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'payment_payment_method_input element',
      'group' => 'Payment',
    );
  }

  /**
   * Creates a payment method.
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected function createPaymentMethod() {
    $payment_method = Generate::createPaymentMethod(2, \Drupal::service('plugin.manager.payment.method')
      ->createInstance('payment_basic')
      ->setMessageText($this->randomName()));
    $payment_method->save();

    return $payment_method;
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $state = \Drupal::state();

    // Test the presence of default elements without available payment methods.
    $this->drupalGet('payment_test-element-payment-method');
    $this->assertNoFieldByName('payment_method[select][payment_method_id]');
    $this->assertNoFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText(t('There are no available payment methods.'));

    // Test the presence of default elements with one available payment method.
    $payment_method_1 = $this->createPaymentMethod();
    $this->drupalGet('payment_test-element-payment-method');
    $this->assertNoFieldByName('payment_method[select][payment_method_id]');
    $this->assertNoFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertNoText(t('There are no available payment methods.'));

    // Test the presence of default elements with multiple available payment
    // methods.
    $payment_method_2 = $this->createPaymentMethod();
    $this->drupalGet('payment_test-element-payment-method');
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertNoText(t('There are no available payment methods.'));

    // Choose a payment method through a regular submission.
    $this->drupalPostForm(NULL, array(
      'payment_method[select][payment_method_id]' => $payment_method_1->id() . ':default',
    ), t('Choose payment method'));
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_1->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_2->getPlugin()->getMessageText());

    // Change the payment method through a regular submission.
    $this->drupalPostForm(NULL, array(
      'payment_method[select][payment_method_id]' => $payment_method_2->id() . ':default',
    ), t('Choose payment method'));
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_2->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_1->getPlugin()->getMessageText());

    // Submit the form through a regular submission.
    $this->drupalPostForm(NULL, array(), t('Submit'));
    $payment_method_id = $state->get('payment_test_method_form_element');
    $this->assertEqual($payment_method_id, $payment_method_2->id());

    // Choose a payment method through an AJAX submission.
    $this->drupalPostAjaxForm('payment_test-element-payment-method', array(
      'payment_method[select][payment_method_id]' => $payment_method_1->id() . ':default',
    ), 'payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_1->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_2->getPlugin()->getMessageText());
    $this->verbose($this->drupalGetContent());

    // Change the payment method through an AJAX submission.
    $this->drupalPostAjaxForm(NULL, array(
      'payment_method[select][payment_method_id]' => $payment_method_2->id() . ':default',
    ), 'payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_2->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_1->getPlugin()->getMessageText());

    // Submit the form through an AJAX submission.
    $this->drupalPostForm(NULL, array(), t('Submit'));
    $payment_method_id = $state->get('payment_test_method_form_element');
    $this->assertEqual($payment_method_id, $payment_method_2->id());
  }
}
