<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentMethod.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment_methodelement.
 */
class PaymentMethod extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment_test');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'payment_method element',
      'group' => 'Payment',
    );
  }

  /**
   * Creates a payment method.
   *
   * @return \Drupal\payment\Plugin\Core\Entity\PaymentMethodInterface
   */
  public function createPaymentMethod() {
    $payment_method = Generate::createPaymentMethod(2, $this->container
      ->get('plugin.manager.payment.payment_method')
      ->createInstance('payment_basic')
      ->setMessageText($this->randomName()));
    $payment_method->save();

    return $payment_method;
  }

  /**
   * Tests a regular submission.
   */
  function testSubmission() {
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

    // Choose a payment method.
    $this->drupalPost(NULL, array(
      'payment_method[select][payment_method_id]' => $payment_method_1->id() . ':default',
    ), t('Choose payment method'));
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_1->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_2->getPlugin()->getMessageText());

    // Change the payment method.
    $this->drupalPost(NULL, array(
      'payment_method[select][payment_method_id]' => $payment_method_2->id() . ':default',
    ), t('Choose payment method'));
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_2->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_1->getPlugin()->getMessageText());

    // Submit the form.
    $this->drupalPost(NULL, array(), t('Submit'));
    $payment_method_id = \Drupal::state()->get('payment_test_method_form_element');
    $this->assertEqual($payment_method_id, $payment_method_2->id());
  }

  /**
   * Tests an AJAX submission.
   */
  function testAjaxSubmission() {
    $payment_method_1 = $this->createPaymentMethod();
    $payment_method_2 = $this->createPaymentMethod();

    // Choose a payment method.
    $this->drupalPostAJAX('payment_test-element-payment-method', array(
      'payment_method[select][payment_method_id]' => $payment_method_1->id() . ':default',
    ), 'payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_1->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_2->getPlugin()->getMessageText());
    $this->verbose($this->drupalGetContent());

    // Change the payment method.
    $this->drupalPostAjax(NULL, array(
      'payment_method[select][payment_method_id]' => $payment_method_2->id() . ':default',
    ), 'payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][payment_method_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_2->getPlugin()->getMessageText());
    $this->assertNoText($payment_method_1->getPlugin()->getMessageText());

    // Submit the form.
    $this->drupalPost(NULL, array(), t('Submit'));
    $payment_method_id = \Drupal::state()->get('payment_test_method_form_element');
    $this->assertEqual($payment_method_id, $payment_method_2->id());
  }
}
