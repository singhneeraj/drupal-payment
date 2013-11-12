<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Cancelled.
 */

namespace Drupal\payment\Plugin\payment\status;

/**
 * A cancelled payment.
 *
 * @PaymentStatus(
 *   id = "payment_cancelled",
 *   label = @Translation("Cancelled"),
 *   parent_id = "payment_failed"
 * )
 */
class Cancelled extends Base {
}
