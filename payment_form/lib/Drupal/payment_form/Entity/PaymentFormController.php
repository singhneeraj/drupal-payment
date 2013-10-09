<?php

/**
 * @file
 * Contains Drupal\payment\Entity\PaymentFormController.
 */

namespace Drupal\payment_form\Entity;

use Drupal\Core\Entity\EntityFormController;
use Drupal\payment\Element\PaymentPaymentMethodInput;

/**
 * Provides the payment form.
 */
class PaymentFormController extends EntityFormController {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $payment = $this->getEntity();
    $form['line_items'] = array(
      '#payment' => $payment,
      '#type' => 'payment_line_items_display',
    );
    $form['payment_method'] = array(
      '#default_value' => clone $payment,
      '#type' => 'payment_payment_method_input',
    );

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $payment = PaymentPaymentMethodInput::getPayment($form['payment_method'], $form_state);
    $payment->save();
    $payment->execute();
  }

  /**
   * Returns an array of supported actions for the current entity form.
   */
  protected function actions(array $form, array &$form_state) {
    // Only use the existing submit action.
    $actions = parent::actions($form, $form_state);
    $actions = array(
      'submit' => $actions['submit'],
    );
    $actions['submit']['#value'] = $this->t('Pay');

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    return $this->getEntity();
  }
}
