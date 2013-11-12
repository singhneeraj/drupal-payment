<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Created.
 */

namespace Drupal\payment\Plugin\payment\status;

/**
 * A newly created payment.
 *
 * @PaymentStatus(
 *   id = "payment_created",
 *   label = @Translation("Created"),
 *   parent_id = "payment_no_money_transferred"
 * )
 */
class Created extends Base {
}
