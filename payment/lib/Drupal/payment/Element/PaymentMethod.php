<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentMethod.
 */

namespace Drupal\payment\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Provides form callbacks for the payment_method form element.
 */
class PaymentMethod {

  /**
   * Implements form #process callback.
   */
  public static function process(array $element, array &$form_state, array $form) {
    // Validate the element configuration.
    if (!($element['#default_value'] instanceof PaymentInterface)) {
      throw new \InvalidArgumentException('The default value does not implement \Drupal\payment\Entity\PaymentInterface.');
    }

    static::initialize($element, $form_state);
    $payment = static::getPayment($element, $form_state);

    // Get available payment methods.
    $payment_method_options = array();
    $payment_method_ids = empty($element['#payment_method_ids']) ? NULL : $element['#payment_method_ids'];
    foreach (entity_load_multiple('payment_method', $payment_method_ids) as $payment_method) {
      foreach ($payment_method->brandOptions() as $brand_name => $label) {
        if ($payment_method->paymentOperationAccess($payment, 'execute', $brand_name)) {
          $payment_method_options[$payment_method->id() . ':' . $brand_name] = $label;
        }
      }
    }

    // There are no available payment methods.
    if (count($payment_method_options ) == 0) {
      $element['pmid_title'] = array(
        '#type' => 'item',
        '#title' => isset($element['#title']) ? $element['#title'] : NULL,
        '#markup' => t('There are no available payment methods.'),
      );
    }
    else {
      // There is one available payment method. Default to it.
      if (count($payment_method_options) == 1) {
        list($payment_method_id, $brand_name) = explode(':', key($payment_method_options));
        $payment->setPaymentMethodId($payment_method_id);
        $payment->setPaymentMethodId($brand_name);
        $element['payment_method_id'] = array(
          '#type' => 'value',
          '#value' => $payment->getPaymentMethodId(),
        );
        if (isset($element['#title'])) {
          $element['pmid_title'] = array(
            '#type' => 'item',
            '#title' => $element['#title'],
            '#markup' => $payment_method_options[$payment->getPaymentMethodId()],
          );
        }
      }
      // There are multiple available payment methods.
      else {
        $element['select']['payment_method_id'] = array(
          '#ajax' => array(
            'effect' => 'fade',
            'event' => 'change',
            'trigger_as' => array(
              'name' => $element['#name'] . '[select][change]',
            ),
            'wrapper' => static::getElementId($element, $form_state),
          ),
          '#default_value' => $payment->getPaymentMethodId() . ':' . $payment->getPaymentMethodBrand(),
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
          '#limit_validation_errors' => array(array_merge($element['#parents'], array('select', 'payment_method_id'))),
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
      if ($payment->getPaymentMethodId()) {
        $element['payment_method_form'] += $payment->getPaymentMethod()->paymentFormElements($form, $form_state, $payment);
      }
    }

    // The element itself has no input, only its children, so mark it not
    // required to prevent validation errors.
    $element['#required'] = FALSE;

    return $element;
  }

  /**
   * Implements form #submit callback.
   */
  public static function submit(array &$form, array &$form_state) {
    $parents = array_slice($form_state['triggering_element']['#parents'], 0, -2);
    $value = NestedArray::getValue($form_state['values'], array_merge($parents, array('select', 'payment_method_id')));
    list($payment_method_id, $brand_name) = explode(':', $value);
    $root_element = NestedArray::getValue($form, $parents);
    $payment = static::getPayment($root_element, $form_state);
    if ($payment->getPaymentMethodId() != $payment_method_id || $payment->getPaymentMethodBrand() != $brand_name) {
      $payment->setPaymentMethodId($payment_method_id)
        ->setPaymentMethodBrand($brand_name);
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
   * Stores the payment in the form's state.
   *
   * @param array $element
   * @param array $form_state
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   */
  protected static function setPayment(array $element, array &$form_state, PaymentInterface $payment) {
    $form_state['payment_method'][$element['#name']]['payment'] = $payment;
  }

  /**
   * Retrieves the payment from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public static function getPayment(array $element, array &$form_state) {
    return $form_state['payment_method'][$element['#name']]['payment'];
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
    return $form_state['payment_method'][$element['#name']]['id'];
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
    if (!(isset($form_state['payment_method']) && array_key_exists($element['#name'], $form_state['payment_method']))) {
      static::setPayment($element, $form_state, $element['#default_value']);
      $form_state['payment_method'][$element['#name']]['id'] = isset($element['#id']) ? $element['#id'] : drupal_html_id('payment_method');
    }
  }
}
