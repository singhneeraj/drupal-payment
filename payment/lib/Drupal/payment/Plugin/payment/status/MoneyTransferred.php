<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\MoneyTransferred.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\payment\Plugin\payment\status\Base;

/**
 * Money has been transferred.
 *
 * @PaymentStatus(
 *   id = "payment_money_transferred",
 *   label = @Translation("Money has been transferred")
 * )
 */
class MoneyTransferred extends Base {
}
