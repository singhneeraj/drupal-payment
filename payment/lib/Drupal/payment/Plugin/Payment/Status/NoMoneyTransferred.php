<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\NoMoneyTransferred.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * No money has been transferred.
 *
 * @PaymentStatus(
 *   id = "payment_no_money_transferred",
 *   label = @Translation("No money has been transferred")
 * )
 */
class NoMoneyTransferred extends Base {
}
