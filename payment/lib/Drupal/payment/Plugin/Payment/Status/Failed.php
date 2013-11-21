<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Failed.
 */

namespace Drupal\payment\Plugin\Payment\Status;

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
