<?php

/**
 * @file
 * A views field handler. See payment_views_data().
 */

/**
 * Views field handler for a payment method's controller description.
 */
class PaymentViewsHandlerFieldPaymentMethodControllerDescription extends views_handler_field {

  /**
   * Implements views_handler_field::render().
   */
  function render($values) {
    $definition = \Drupal::service('plugin.manager.payment.payment_method')->getDefinition($this->getValue($values));

    return $definition['description'];
  }
}
