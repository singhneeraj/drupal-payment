<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Unknown.
 */

namespace Drupal\payment\Plugin\payment\status;

/**
 * An unknown payment status.
 *
 * @PaymentStatus(
 *   description = @Translation("The payment status could not be automatically verified."),
 *   id = "payment_unknown",
 *   label = @Translation("Unknown")
 * )
 */
class Unknown extends Base {
}
