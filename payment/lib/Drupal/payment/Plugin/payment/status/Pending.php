<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Pending.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentStatus;
use Drupal\payment\Plugin\payment\status\Base;

/**
 * An pending payment status.
 *
 * @PaymentStatus(
 *   id = "payment_pending",
 *   label = @Translation("Pending"),
 *   parentId = "payment_no_money_transferred"
 * )
 */
class Pending extends Base {
}
