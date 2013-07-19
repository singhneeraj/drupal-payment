<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentLineItemPaymentBasicFormElement.
 */

namespace Drupal\payment_test;

use Drupal\Core\Controller\ControllerInterface;
use Drupal\Core\Form\FormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentLineItemPaymentBasicFormElements implements ControllerInterface, FormInterface {

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
    return 'payment_test-payment-line_item-payment_basic';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $manager = \Drupal::service('plugin.manager.payment.line_item');
    $line_item = $manager->createInstance('payment_basic');
    $form['line_item'] = $line_item->formElements($form, $form_state);
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
    $form_state['redirect'] = 'user';
  }
}
