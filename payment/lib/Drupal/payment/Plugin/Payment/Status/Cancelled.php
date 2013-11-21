<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Cancelled.
 */

namespace Drupal\payment\Plugin\Payment\Status;

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
