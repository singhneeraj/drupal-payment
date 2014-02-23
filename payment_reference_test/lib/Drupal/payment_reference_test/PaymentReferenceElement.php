<?php

/**
 * @file
 * Contains \Drupal\payment_reference_test\PaymentReferenceElement.
 */

namespace Drupal\payment_reference_test;

use Drupal\Core\Form\FormInterface;
use Drupal\payment\Generate;

/**
 * Provides a form for testing the payment_reference element.
 */
class PaymentReferenceElement implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_reference_test_payment_reference_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['payment_reference'] = array(
      // The ID of the field instance the element is used for.
      '#field_instance_config_id' => 'payment_reference_test_payment_reference_element',
      // The ID of the account that must own the payment.
      '#owner_id' => 2,
      // An array of
      // \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
      // instances.
      '#payment_line_items' => Generate::createPaymentLineItems(),
      '#payment_currency_code' => 'EUR',
      '#required' => TRUE,
      '#title' => 'Foo',
      '#type' => 'payment_reference',
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    \Drupal::state()->set('payment_reference_test_payment_reference_element', $form_state['values']['payment_reference']);
  }
}
