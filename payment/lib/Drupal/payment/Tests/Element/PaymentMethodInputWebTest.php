<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentMethodInputWebTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Generate;
use Drupal\payment\Payment;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment_method_input element.
 */
class PaymentMethodInputWebTest extends WebTestBase {

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
      'name' => 'payment_method_input element',
      'group' => 'Payment',
    );
  }

  /**
   * Creates a payment method.
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected function createPaymentMethod() {
    $payment_method = Generate::createPaymentMethod(2, 'payment_basic');
    $payment_method->setPluginConfiguration(array(
      'brand_label' => $this->randomName(),
      'message_text' => $this->randomName(),
    ));
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
    $this->assertNoFieldByName('payment_method[select][payment_method_plugin_id]');
    $this->assertNoFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText(t('There are no available payment methods.'));

    // Test the presence of default elements with one available payment method.
    $payment_method_1 = $this->createPaymentMethod();
    Payment::methodManager()->clearCachedDefinitions();
    $this->drupalGet('payment_test-element-payment-method');
    $this->assertNoFieldByName('payment_method[select][payment_method_plugin_id]');
    $this->assertNoFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertNoText(t('There are no available payment methods.'));

    // Test the presence of default elements with multiple available payment
    // methods.
    $payment_method_2 = $this->createPaymentMethod();
    Payment::methodManager()->clearCachedDefinitions();
    $this->drupalGet('payment_test-element-payment-method');
    $this->assertFieldByName('payment_method[select][payment_method_plugin_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertNoText(t('There are no available payment methods.'));

    // Choose a payment method through a regular submission.
    $this->drupalPostForm(NULL, array(
      'payment_method[select][payment_method_plugin_id]' => 'payment_basic:' . $payment_method_1->id(),
    ), t('Choose payment method'));
    $this->assertFieldByName('payment_method[select][payment_method_plugin_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $payment_method_1_configuration = $payment_method_1->getPluginConfiguration();
    $this->assertText($payment_method_1_configuration['message_text']);
    $payment_method_2_configuration = $payment_method_2->getPluginConfiguration();
    $this->assertNoText($payment_method_2_configuration['message_text']);

    // Change the payment method through a regular submission.
    $this->drupalPostForm(NULL, array(
      'payment_method[select][payment_method_plugin_id]' => 'payment_basic:' . $payment_method_2->id(),
    ), t('Choose payment method'));
    $this->assertFieldByName('payment_method[select][payment_method_plugin_id]');
    $this->assertFieldByName('payment_method[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_2_configuration['message_text']);
    $this->assertNoText($payment_method_1_configuration['message_text']);

    // Submit the form through a regular submission.
    $this->drupalPostForm(NULL, array(), t('Submit'));
    $payment_method_data = $state->get('payment_test_method_form_element');
    $this->assertEqual($payment_method_data['plugin_id'], 'payment_basic:' . $payment_method_2->id());
  }
}
