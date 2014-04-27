<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\MoneyTransferred.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * Money has been transferred.
 *
 * @PaymentStatus(
 *   id = "payment_money_transferred",
 *   label = @Translation("Money has been transferred")
 * )
 */
class MoneyTransferred extends PaymentStatusBase {
}
