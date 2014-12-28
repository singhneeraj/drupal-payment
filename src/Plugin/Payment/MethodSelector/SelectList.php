<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\MethodSelector\SelectList.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a payment method selector using a <select> element.
 *
 * @PaymentMethodSelector(
 *   id = "payment_select_list",
 *   label = @Translation("Drop-down selection list")
 * )
 */
class SelectList extends AdvancedPaymentMethodSelectorBase {

  /**
   * {@inheritdoc}
   */
  protected function buildSelector(array $root_element, FormStateInterface $form_state, array $payment_methods) {
    $element = parent::buildSelector($root_element, $form_state, $payment_methods);
    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[] $payment_methods */
    $payment_method_options = [];
    foreach ($payment_methods as $payment_method) {
      $payment_method_options[$payment_method->getPluginId()] = $payment_method->getPluginLabel();
    }
    $element['container']['payment_method_id'] = array(
      '#ajax' => array(
        'callback' => array(get_class(), 'ajaxSubmitConfigurationForm'),
        'effect' => 'fade',
        'event' => 'change',
        'trigger_as' => array(
          'name' => $element['container']['change']['#name'],
        ),
        'wrapper' => $this->getElementId(),
      ),
      '#default_value' => is_null($this->getPaymentMethod()) ? NULL : $this->getPaymentMethod()->getPluginId(),
      '#empty_value' => 'select',
      '#options' => $payment_method_options ,
      '#required' => $this->isRequired(),
      '#title' => $this->t('Payment method'),
      '#type' => 'select',
    );

    return $element;
  }

}
