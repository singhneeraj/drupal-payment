<?php

/**
 * @file
 * Contains \Drupal\payment_reference_test\PaymentReferenceElement.
 */

namespace Drupal\payment_reference_test;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\payment\Tests\Generate;

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['payment_reference'] = array(
      '#entity_type_id' => 'user',
      '#bundle' => 'user',
      '#field_name' => 'foobarbaz',
      '#owner_id' => 2,
      '#payment_line_items' => Generate::createPaymentLineItems(),
      '#payment_currency_code' => 'EUR',
      '#required' => TRUE,
      '#title' => 'FooBarBaz',
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('payment_reference_test_payment_reference_element', $form_state->getValues()['payment_reference']);
  }
}
