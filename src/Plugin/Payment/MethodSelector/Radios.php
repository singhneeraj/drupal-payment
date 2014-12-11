<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\MethodSelector\Radios.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a payment method selector using a radio buttons.
 *
 * @PaymentMethodSelector(
 *   id = "payment_radios",
 *   label = @Translation("Radio buttons")
 * )
 */
class Radios extends AdvancedPaymentMethodSelectorBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['clear'] = array(
      '#markup' => '<div style="clear: both;"></div>',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildSelector(array $root_element, FormStateInterface $form_state, array $payment_methods) {
    $element = parent::buildSelector($root_element, $form_state, $payment_methods);
    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[] $payment_methods */
    $payment_method_options = array();
    foreach ($payment_methods as $payment_method) {
      $payment_method_options[$payment_method->getPluginId()] = $payment_method->getPluginLabel();
    }
    $element['container']['payment_method_id'] = array(
      '#ajax' => array(
        'callback' => array(get_class(), 'ajaxSubmitConfigurationForm'),
        'effect' => 'fade',
        'event' => 'change',
        'progress' => 'none',
        'trigger_as' => array(
          'name' => $element['container']['change']['#name'],
        ),
        'wrapper' => $this->getElementId(),
      ),
      '#attached' => [
        'library' => ['payment/payment_method_selector.payment_radios'],
      ],
      '#default_value' => is_null($this->getPaymentMethod()) ? NULL : $this->getPaymentMethod()->getPluginId(),
      '#empty_value' => 'select',
      '#options' => $payment_method_options ,
      '#required' => $this->isRequired(),
      '#title' => $this->t('Payment method'),
      '#type' => 'radios',
    );

    return $element;
  }

}
