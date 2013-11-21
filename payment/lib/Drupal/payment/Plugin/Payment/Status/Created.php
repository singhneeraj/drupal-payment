<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Created.
 */

namespace Drupal\payment\Plugin\Payment\Status;

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
