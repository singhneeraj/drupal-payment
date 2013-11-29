<?php

/**
 * @file
 * Contains \Drupal\payment_reference\PaymentReference.
 */

namespace Drupal\payment_reference;

/**
 * Static service container wrapper for Payment Reference Field.
 */
class PaymentReference {

  /**
   * Returns the payment reference queue service.
   *
   * @return \Drupal\payment_reference\QueueInterface
   */
  public static function queue() {
    return \Drupal::service('payment_reference.queue');
  }

}
