<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Expired.
 */

namespace Drupal\payment\Plugin\Payment\Status;

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
