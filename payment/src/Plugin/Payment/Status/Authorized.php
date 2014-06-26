<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Authorized.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * An authorized payment.
 *
 * @PaymentStatus(
 *   description = @Translation("The payment has been authorized by the payer, and the funds are waiting to be transferred."),
 *   id = "payment_authorized",
 *   label = @Translation("Authorized"),
 *   parent_id = "payment_no_money_transferred"
 * )
 */
class Authorized extends PaymentStatusBase {
}
