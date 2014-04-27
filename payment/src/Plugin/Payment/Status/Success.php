<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Success.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * An unknown payment status.
 *
 * @PaymentStatus(
 *   id = "payment_success",
 *   label = @Translation("Completed"),
 *   parent_id = "payment_money_transferred"
 * )
 */
class Success extends PaymentStatusBase {
}
