<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Success.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\payment\Plugin\payment\status\Base;

/**
 * An unknown payment status.
 *
 * @PaymentStatus(
 *   id = "payment_success",
 *   label = @Translation("Completed"),
 *   parent_id = "payment_money_transferred"
 * )
 */
class Success extends Base {
}
