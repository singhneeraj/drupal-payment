<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Action\UnsetLineItem.
 */

namespace Drupal\payment\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Sets a status on a payment.
 *
 * @Action(
 *   id = "payment_line_item_unset",
 *   label = @Translation("Delete a line item"),
 *   type = "payment"
 * )
 */
class UnsetLineItem extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(PaymentInterface $payment = NULL) {
    if ($payment) {
      $payment->unsetLineItem($this->configuration['line_item_name']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'line_item_name' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form['line_item_name'] = array(
      '#default_value' => $this->configuration['line_item_name'],
      '#required' => TRUE,
      '#title' => $this->t('Line item name'),
      '#type' => 'textfield',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['line_item_name'] = $form_state['values']['line_item_name'];
  }

}
