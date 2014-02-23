<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentLineItemElement.
 */

namespace Drupal\payment_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\payment\Element\PaymentLineItemsInput;
use Drupal\payment\Generate;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentLineItemElement implements ContainerInjectionInterface, FormInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_test_payment_line_item_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $line_item_data = array();
    foreach (Generate::createPaymentLineItems() as $line_item) {
      $line_item_data[] = array(
        'plugin_configuration' => $line_item->getConfiguration(),
        'plugin_id' => $line_item->getPluginId(),
      );
    }
    // Nest the element to make sure that works.
    $form['container']['line_item'] = array(
      '#cardinality' => 4,
      '#default_value' => $line_item_data,
      '#type' => 'payment_line_items_input',
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
    \Drupal::state()->set('payment_test_line_item_form_element', PaymentLineItemsInput::getLineItemsData($form['container']['line_item'], $form_state));
  }
}
