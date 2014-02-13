<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Utility\NestedArray;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Provides a payment selector using a <select> element.
 *
 * @PaymentMethodSelector(
 *   id = "payment_select",
 *   label = @Translation("Drop-down selector")
 * )
 */
class PaymentSelect extends PaymentMethodSelectorBase {

  /**
   * {@inheritdoc}
   */
  public function formElements(array $form, array &$form_state, PaymentInterface $payment) {
    $elements = array(
      '#input' => TRUE,
      '#payment' => $payment,
      '#process' => array(array($this, 'process')),
      '#tree' => TRUE,
      // Use a dummy #type, because the form builder expects it.
      '#type' => FALSE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethodFromFormElements(array $form, array &$form_state) {
    $method_data = $this->getPaymentMethodData($form, $form_state);
    if ($this->paymentMethodManager->getDefinition($method_data['plugin_id'])) {
      return $this->paymentMethodManager->createInstance($method_data['plugin_id'], $method_data['plugin_configuration']);
    }
    return NULL;
  }

  /**
   * Implements form #process callback.
   *
   * @see self::formElements()
   */
  public function process(array $element, array &$form_state, array $form) {
    // Unset the payment entity, so it does not end up in the cache.
    $payment = $element['#payment'];
    unset($element['#payment']);

    $this->initialize($element, $form_state, $payment);
    $selected_payment_method = $this->getPaymentMethodFromFormElements($element, $form_state);
    $available_payment_methods = $this->getAvailablePaymentMethods($payment);

    // There are no available payment methods.
    if (count($available_payment_methods) == 0) {
      $element['payment_method_label'] = array(
        '#type' => 'item',
        '#title' => isset($element['#title']) ? $element['#title'] : NULL,
        '#markup' => t('There are no available payment methods.'),
      );
    }
    else {
      // There is one available payment method.
      if (count($available_payment_methods) == 1) {
        // Use the only available payment method if no other was configured
        // before, or the configured payment method is not available.
        if (is_null($selected_payment_method) || $selected_payment_method->getPluginId() != reset($available_payment_methods)->getPluginId()) {
          $selected_payment_method = reset($available_payment_methods);
          $this->setPaymentMethodData($element, $form_state, $selected_payment_method->getPluginId(), $selected_payment_method->getConfiguration());
        }

        $element['payment_method_plugin_id'] = array(
          '#type' => 'value',
          '#value' => $selected_payment_method->getPluginId(),
        );
        if (isset($element['#title'])) {
          $element['payment_method_label'] = array(
            '#type' => 'item',
            '#title' => $element['#title'],
            '#markup' => $selected_payment_method->getPluginId(),
          );
        }
      }
      // There are multiple available payment methods.
      else {
        $payment_method_options = array();
        foreach ($available_payment_methods as $plugin_id => $payment_method) {
          $payment_method_options[$plugin_id] = $payment_method->getPluginLabel();
        }
        $element['select']['payment_method_plugin_id'] = array(
          '#ajax' => array(
            'effect' => 'fade',
            'event' => 'change',
            'trigger_as' => array(
              'name' => $element['#name'] . '[select][change]',
            ),
            'wrapper' => $this->getElementId($element, $form_state),
          ),
          '#default_value' => is_null($selected_payment_method) ? NULL : $selected_payment_method->getPluginId(),
          '#empty_value' => 'select',
          '#options' => $payment_method_options ,
          '#required' => $element['#required'],
          '#title' => isset($element['#title']) ? $element['#title'] : NULL,
          '#type' => 'select',
        );
        $element['select']['change'] = array(
          '#ajax' => array(
            'callback' => array($this, 'ajaxSubmit'),
          ),
          '#attributes' => array(
            'class' => array('js-hide')
          ),
          '#limit_validation_errors' => array(array_merge($element['#parents'], array('select', 'payment_method_plugin_id'))),
          '#name' => $element['#name'] . '[select][change]',
          '#submit' => array(array($this, 'submit')),
          '#type' => 'submit',
          '#value' => t('Choose payment method'),
        );
      }
      $element['payment_method_form'] = array(
        '#id' => $this->getElementId($element, $form_state),
        '#type' => 'container',
      );

      $element['payment_method_form'] = is_null($selected_payment_method) ? array() : $selected_payment_method->formElements($form, $form_state, $payment);
    }

    // The element itself has no input, but only its children, so mark it not
    // required to prevent validation errors.
    $element['#required'] = FALSE;

    return $element;
  }

  /**
   * Implements form #submit callback.
   */
  public function submit(array &$form, array &$form_state) {
    $parents = array_slice($form_state['triggering_element']['#parents'], 0, -2);
    $payment_method_plugin_id = NestedArray::getValue($form_state['values'], array_merge($parents, array('select', 'payment_method_plugin_id')));
    $root_element = NestedArray::getValue($form, $parents);
    $payment_method_data = $this->getPaymentMethodData($root_element, $form_state);
    if ($payment_method_data['plugin_id'] != $payment_method_plugin_id) {
      $this->setPaymentMethodData($root_element, $form_state, $payment_method_plugin_id);
    }

    $form_state['rebuild'] = TRUE;
  }

  /**
   * Implements form AJAX callback.
   */
  public function ajaxSubmit(array &$form, array &$form_state) {
    $parents = array_slice($form_state['triggering_element']['#parents'], 0, -2);
    $root_element = NestedArray::getValue($form, $parents);

    return $root_element['payment_method_form'];
  }

  /**
   * Stores the payment method data in the form's state.
   *
   * @param array $element
   * @param array $form_state
   * @param string $payment_method_plugin_id
   * @param array $payment_method_plugin_configuration
   */
  protected function setPaymentMethodData(array $element, array &$form_state, $payment_method_plugin_id, array $payment_method_plugin_configuration = array()) {
    $form_state[$this->getPluginId()][$element['#name']]['payment_method_data'] = array(
      'plugin_configuration' => $payment_method_plugin_configuration,
      'plugin_id' => $payment_method_plugin_id,
    );
  }

  /**
   * Retrieves the payment method data from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return array
   *   Keys are:
   *   - plugin_id: The payment method plugin's ID.
   *   - plugin_configuration: An array with tThe payment method plugin's
   *     configuration.
   */
  protected function getPaymentMethodData(array $element, array &$form_state) {
    return $form_state[$this->getPluginId()][$element['#name']]['payment_method_data'];
  }

  /**
   * Retrieves the element's ID from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return string
   */
  protected function getElementId(array $element, array &$form_state) {
    return $form_state[$this->getPluginId()][$element['#name']]['element_id'];
  }

  /**
   * Check if the form's state has been initialized for an element.
   *
   * @param array $element
   * @param array $form_state
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return bool
   */
  protected function initialize(array $element, array &$form_state, PaymentInterface $payment) {
    if (!(isset($form_state[$this->getPluginId()]) && array_key_exists($element['#name'], $form_state[$this->getPluginId()]))) {
      $plugin_configuration = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getConfiguration() : array();
      $plugin_id = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginId() : NULL;
      $this->setPaymentMethodData($element, $form_state, $plugin_id, $plugin_configuration);
      $form_state[$this->getPluginId()][$element['#name']]['element_id'] = isset($element['#id']) ? $element['#id'] : drupal_html_id($this->getPluginId());
    }
  }
}
