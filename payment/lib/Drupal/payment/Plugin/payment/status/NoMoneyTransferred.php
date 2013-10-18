<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\NoMoneyTransferred.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\payment\Plugin\payment\status\Base;

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
