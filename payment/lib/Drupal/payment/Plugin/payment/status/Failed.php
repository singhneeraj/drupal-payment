<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Failed.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\payment\Plugin\payment\status\Base;

/**
 * A failed payment.
 *
 * @PaymentStatus(
 *   id = "payment_failed",
 *   label = @Translation("Failed"),
 *   parent_id = "payment_no_money_transferred"
 * )
 */
class Failed extends Base {
}
