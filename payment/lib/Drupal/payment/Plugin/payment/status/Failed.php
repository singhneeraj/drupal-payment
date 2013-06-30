<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Failed.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentStatus;
use Drupal\payment\Plugin\payment\status\Base;

/**
 * A failed payment.
 *
 * @PaymentStatus(
 *   id = "payment_failed",
 *   label = @Translation("Failed"),
 *   parentId = "payment_no_money_transferred"
 * )
 */
class Failed extends Base {
}
