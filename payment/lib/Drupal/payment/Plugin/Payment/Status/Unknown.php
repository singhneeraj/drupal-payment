<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Unknown.
 */

namespace Drupal\payment\Plugin\Payment\Status;

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
