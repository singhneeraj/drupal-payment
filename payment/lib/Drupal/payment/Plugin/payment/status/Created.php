<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Created.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentStatus;
use Drupal\payment\Plugin\payment\status\Base;

/**
 * A newly created payment.
 *
 * @PaymentStatus(
 *   id = "payment_created",
 *   label = @Translation("Created"),
 *   parentId = "payment_no_money_transferred"
 * )
 */
class Created extends Base {
}
