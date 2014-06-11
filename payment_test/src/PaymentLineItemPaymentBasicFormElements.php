<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentLineItemPaymentBasicFormElement.
 */

namespace Drupal\payment_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\payment\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentLineItemPaymentBasicFormElements implements ContainerInjectionInterface, FormInterface {

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
    return 'payment_test-payment-line_item-payment_basic';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    if (isset($form_state['storage']['payment_line_item'])) {
      $line_item = $form_state['storage']['payment_line_item'];
    }
    else {
      $line_item = Payment::lineItemManager()->createInstance('payment_basic');
      $form_state['storage']['payment_line_item'] = $line_item;
    }
    $form['line_item'] = $line_item->buildConfigurationForm(array(), $form_state);
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
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
    $line_item = $form_state['storage']['payment_line_item'];
    $line_item->validateConfigurationForm($form['line_item'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
    $line_item = $form_state['storage']['payment_line_item'];
    $line_item->submitConfigurationForm($form['line_item'], $form_state);
    $form_state['redirect_route'] = array(
      'route_name' => 'user.page',
    );
  }
}
