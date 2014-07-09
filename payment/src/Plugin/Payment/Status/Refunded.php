<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Refunded.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * A refunded payment.
 *
 * @PaymentStatus(
 *   id = "payment_refunded",
 *   label = @Translation("Refunded"),
 *   parent_id = "payment_no_money_transferred"
 * )
 */
class Refunded extends PaymentStatusBase {
}
