<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Expired.
 */

namespace Drupal\payment\Plugin\payment\status;

/**
 * An expired payment.
 *
 * @PaymentStatus(
 *   id = "payment_expired",
 *   label = @Translation("Expired"),
 *   parent_id = "payment_failed"
 * )
 */
class Expired extends Base {
}
