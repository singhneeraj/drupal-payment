<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Expired.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentStatus;
use Drupal\payment\Plugin\payment\status\Base;

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
