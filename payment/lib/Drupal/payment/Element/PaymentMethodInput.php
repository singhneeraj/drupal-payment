<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentMethodInput.
 */

namespace Drupal\payment\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\payment\Payment;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\Validator\Constraints\False;

/**
 * Provides form callbacks for the payment_method_input form element.
 */
class PaymentMethodInput {

  /**
   * Implements form #process callback.
   */
  public static function process(array $element, array &$form_state, array $form) {
    // Validate the element configuration.
    if (!($element['#payment'] instanceof PaymentInterface)) {
      throw new \InvalidArgumentException('The payment must implement \Drupal\payment\Entity\PaymentInterface.');
    }

    static::initialize($element, $form_state);
    $payment = $element['#payment'];
    // Unset the payment entity, so it does not end up in the cache.
    unset($element['#payment']);
    $selected_payment_method = static::getPaymentMethod($element, $form_state);
    $available_payment_methods = static::getAvailablePaymentMethods($payment, $element['#limit_payment_method_plugin_ids']);

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
          static::setPaymentMethodData($element, $form_state, $selected_payment_method->getPluginId(), $selected_payment_method->getConfiguration());
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
            'wrapper' => static::getElementId($element, $form_state),
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
            'callback' => array(get_class(), 'ajaxSubmit'),
          ),
          '#attributes' => array(
            'class' => array('js-hide')
          ),
          '#limit_validation_errors' => array(array_merge($element['#parents'], array('select', 'payment_method_plugin_id'))),
          '#name' => $element['#name'] . '[select][change]',
          '#submit' => array(array(get_class(), 'submit')),
          '#type' => 'submit',
          '#value' => t('Choose payment method'),
        );
      }
      $element['payment_method_form'] = array(
        '#id' => static::getElementId($element, $form_state),
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
  public static function submit(array &$form, array &$form_state) {
    $parents = array_slice($form_state['triggering_element']['#parents'], 0, -2);
    $payment_method_plugin_id = NestedArray::getValue($form_state['values'], array_merge($parents, array('select', 'payment_method_plugin_id')));
    $root_element = NestedArray::getValue($form, $parents);
    $payment_method_data = static::getPaymentMethodData($root_element, $form_state);
    if ($payment_method_data['plugin_id'] != $payment_method_plugin_id) {
      static::setPaymentMethodData($root_element, $form_state, $payment_method_plugin_id);
    }

    $form_state['rebuild'] = TRUE;
  }

  /**
   * Implements form AJAX callback.
   */
  public static function ajaxSubmit(array &$form, array &$form_state) {
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
  protected static function setPaymentMethodData(array $element, array &$form_state, $payment_method_plugin_id, array $payment_method_plugin_configuration = array()) {
    $form_state['payment_method_input'][$element['#name']]['payment'] = array(
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
  public static function getPaymentMethodData(array $element, array &$form_state) {
    return $form_state['payment_method_input'][$element['#name']]['payment'];
  }

  /**
   * Retrieves the payment method from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface|null
   */
  public static function getPaymentMethod(array $element, array &$form_state) {
    $method_data = static::getPaymentMethodData($element, $form_state);
    $manager = Payment::methodManager();
    if ($manager->getDefinition($method_data['plugin_id'])) {
      return $manager->createInstance($method_data['plugin_id'], $method_data['plugin_configuration']);
    }
  }

  /**
   * Retrieves the element's ID from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return string
   */
  protected static function getElementId(array $element, array &$form_state) {
    return $form_state['payment_method_input'][$element['#name']]['id'];
  }

  /**
   * Check if the form's state has been initialized for an element.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return bool
   */
  protected static function initialize(array $element, array &$form_state) {
    if (!(isset($form_state['payment_method_input']) && array_key_exists($element['#name'], $form_state['payment_method_input']))) {
      $payment = $element['#payment'];
      $plugin_configuration = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getConfiguration() : array();
      $plugin_id = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginId() : NULL;
      static::setPaymentMethodData($element, $form_state, $plugin_id, $plugin_configuration);
      $form_state['payment_method_input'][$element['#name']]['id'] = isset($element['#id']) ? $element['#id'] : drupal_html_id('payment_method');
    }
  }

  /**
   * Returns all available payment methods for a Payment.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param array|null $limit_payment_method_plugin_ids
   *   An array with IDs of the payment method plugins to which to limit the
   *   selection. Set to NULL to allow all.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[]
   *    An array of payment method plugin instances, keyed by plugin ID.
   */
  protected static function getAvailablePaymentMethods(PaymentInterface $payment, array $limit_payment_method_plugin_ids = NULL) {
    $manager = Payment::methodManager();
    $payment_methods = array();
    foreach (array_keys($manager->getDefinitions()) as $plugin_id) {
      $payment_method = $manager->createInstance($plugin_id);
      if ((is_null($limit_payment_method_plugin_ids) || in_array($plugin_id, $limit_payment_method_plugin_ids))
        && $payment_method->executePaymentAccess($payment, \Drupal::currentUser())) {
        $payment_methods[$payment_method->getPluginId()] = $payment_method;
      }
    }

    return $payment_methods;
  }
}
