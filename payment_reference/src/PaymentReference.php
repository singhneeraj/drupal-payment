<?php

/**
 * @file
 * Contains \Drupal\payment_reference\PaymentReference.
 */

namespace Drupal\payment_reference;

/**
 * Provides wrappers for services.
 */
class PaymentReference {

  /**
   * Returns the payment reference queue.
   *
   * @return \Drupal\payment\QueueInterface
   */
  public static function queue() {
    return \Drupal::service('payment_reference.queue');
  }

}
