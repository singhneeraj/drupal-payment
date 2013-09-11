<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Cancelled.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentStatus;
use Drupal\payment\Plugin\payment\status\Base;

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
