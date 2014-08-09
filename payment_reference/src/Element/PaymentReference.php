<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Element\PaymentReference.
 */

namespace Drupal\payment_reference\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\payment\Entity\Payment;
use Drupal\payment\Payment as PaymentServiceWrapper;
use Drupal\payment_reference\PaymentReference as PaymentReferenceServiceWrapper;

/**
 * Provides form callbacks for the payment_reference form element.
 */
class PaymentReference {

  /**
   * Implements form #process callback.
   */
  public static function process(array $element, FormStateInterface $form_state, array $form) {
    // Validate the element's configuration.
    if (!is_string($element['#bundle'])) {
      throw new \InvalidArgumentException('#bundle must be a string, but ' . gettype($element['#bundle']) . ' was given.');
    }
    if (!is_int($element['#default_value']) && !is_null($element['#default_value'])) {
      throw new \InvalidArgumentException('The default value must be an integer or NULL, but ' . gettype($element['#default_value']) . ' was given.');
    }
    if (!is_string($element['#entity_type_id'])) {
      throw new \InvalidArgumentException('#entity_type_id must be a string, but ' . gettype($element['#entity_type_id']) . ' was given.');
    }
    if (!is_string($element['#field_name'])) {
      throw new \InvalidArgumentException('#field_name must be a string, but ' . gettype($element['#field_name']) . ' was given.');
    }
    if (!is_int($element['#owner_id'])) {
      throw new \InvalidArgumentException('The owner ID must be an integer, but ' . gettype($element['#owner_id']) . ' was given.');
    }
    if (!is_string($element['#payment_currency_code'])) {
      throw new \InvalidArgumentException('The currency code must be a string, but ' . gettype($element['#payment_currency_code']) . ' was given.');
    }

    // Find the default payment to use.
    $payment_id = $element['#default_value'];
    if (!$payment_id) {
      $payment_ids = PaymentReferenceServiceWrapper::queue()->loadPaymentIds($element['#entity_type_id'] . '.' . $element['#bundle'] . '.' . $element['#field_name'], $element['#owner_id']);
      $payment_id = reset($payment_ids);
    }
    // Form API considers an empty string to be an empty value, but not NULL.
    $element['#value'] = $payment_id ? $payment_id : '';

    // AJAX.
    $ajax_wrapper_id = drupal_html_id('payment_reference-' . $element['#name']);
    $element['#prefix'] = '<div id="' . $ajax_wrapper_id . '">';
    $element['#suffix'] = '</div>';
    $element['#attached']['js'] = array(
      drupal_get_path('module', 'payment_reference') . '/js/payment_reference.js',
      array(
      'type' => 'setting',
        'data' => array(
          'PaymentReferencePaymentAvailable' => array(
            $ajax_wrapper_id => !empty($payment_id),
          ),
        ),
      ),
    );

    // Payment information.
    $element['payment'] = array(
      '#empty' => \Drupal::translation()->translate('There are no line items.'),
      '#header' => array(\Drupal::translation()->translate('Amount'), \Drupal::translation()->translate('Status'), \Drupal::translation()->translate('Last updated')),
      '#type' => 'table',
    );
    if (!$payment_id) {
      $amount = 0;
      foreach ($element['#payment_line_items_data'] as $line_item_data) {
        $line_item = PaymentServiceWrapper::lineItemManager()->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']);
        $amount += $line_item->getTotalAmount();
      }
      /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
      $currency = entity_load('currency', $element['#payment_currency_code']);
      $element['payment'][0]['amount'] = array(
        '#markup' => $currency->formatAmount($amount),
      );
      $element['payment'][0]['add'] = array(
        '#attributes' => array(
          'colspan' => 2,
        ),
        '#markup' => \Drupal::translation()->translate('<a href="@url" target="_blank">Add a new payment</a> (opens in a new window)', array(
          '@url' => \Drupal::urlGenerator()->generateFromRoute('payment_reference.pay', array(
              'bundle' => $element['#bundle'],
              'entity_type_id' => $element['#entity_type_id'],
              'field_name' => $element['#field_name'],
          )),
        )),
      );
    }
    else {
      /** @var \Drupal\payment\Entity\PaymentInterface $payment */
      $payment = Payment::load($payment_id);
      /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
      $currency = entity_load('currency', $payment->getCurrencyCode());
      $status = $payment->getStatus();
      $status_definition = $status->getPluginDefinition();
      $element['payment'][0]['amount'] = array(
        '#markup' => $currency->formatAmount($payment->getAmount()),
      );
      $element['payment'][0]['status'] = array(
        '#markup' => $status_definition['label'],
      );
      $element['payment'][0]['updated'] = array(
        '#markup' => format_date($status->getCreated()),
      );
      if ($payment->access('view')) {
        $uri = $payment->urlInfo();
        $element['payment']['header'][] = \Drupal::translation()->translate('Operations');
        $element['payment'][0]['view'] = \Drupal::translation()->translate('<a href="@url" target="_blank">View payment details</a> (opens in a new window)', array(
          '@url' => url($uri['path'], $uri['options']),
        ));
      }
    }

    // Refresh button.
    $element['refresh'] = array(
      '#type' => 'submit',
      '#value' => \Drupal::translation()->translate('Re-check available payments'),
      '#submit' => isset($element['#submit']) ? $element['#submit'] : array(),
      '#limit_validation_errors' => array(),
      '#ajax' => array(
        'callback' => 'payment_reference_form_process_payment_reference_ajax_callback',
        'effect' => 'fade',
        'event' => 'mousedown',
        'wrapper' => $ajax_wrapper_id,
        'progress' => array(),
      ),
      '#attributes' => array(
        'class' => array('payment_reference-refresh-button', 'js-hide'),
      ),
      '#name' => $element['#name'] . '_refresh',
    );
    $form_state->set($element['refresh']['#name'], $element['#parents']);

    return $element;
  }
}
