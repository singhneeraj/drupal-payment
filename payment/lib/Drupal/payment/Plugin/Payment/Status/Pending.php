<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Pending.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * An pending payment status.
 *
 * @PaymentStatus(
 *   id = "payment_pending",
 *   label = @Translation("Pending"),
 *   parent_id = "payment_no_money_transferred"
 * )
 */
class Pending extends Base {
}
