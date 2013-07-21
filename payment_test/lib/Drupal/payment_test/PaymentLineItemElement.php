<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentLineItemElement.
 */

namespace Drupal\payment_test;

use Drupal\Core\Controller\ControllerInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\payment\Element\PaymentLineItem;
use Drupal\payment\Generate;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentLineItemElement implements ControllerInterface, FormInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'payment_test_payment_line_item_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['line_item'] = array(
      '#cardinality' => 4,
      '#default_value' => Generate::createPaymentLineItems(),
      '#type' => 'payment_line_items',
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
    $value = \Drupal::state()->set('payment_test_line_item_form_element', PaymentLineItem::getLineItems($form['line_item'], $form_state));
  }
}
