<?php

/**
 * @file
 * Contains \Drupal\payment\Payment.
 */

namespace Drupal\payment;

/**
 * Provides wrappers for services.
 */
class Payment {

  /**
   * Returns the payment method manager.
   *
   * @return \Drupal\payment\Plugin\payment\method\Manager
   */
  public static function methodManager() {
    return \Drupal::service('plugin.manager.payment.method');
  }

  /**
   * Returns the payment line item manager.
   *
   * @return \Drupal\payment\Plugin\payment\line_item\Manager
   */
  public static function lineItemManager() {
    return \Drupal::service('plugin.manager.payment.line_item');
  }

  /**
   * Returns the payment status manager.
   *
   * @return \Drupal\payment\Plugin\payment\status\Manager
   */
  public static function statusManager() {
    return \Drupal::service('plugin.manager.payment.status');
  }

  /**
   * Returns the payment type manager.
   *
   * @return \Drupal\payment\Plugin\payment\type\Manager
   */
  public static function typeManager() {
    return \Drupal::service('plugin.manager.payment.type');
  }

}
