<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\AuthorizationFailed.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentStatus;
use Drupal\payment\Plugin\payment\status\Base;

/**
 * A payment that failed authorization.
 *
 * @PaymentStatus(
 *   id = "payment_authorization_failed",
 *   label = @Translation("Authorization failed"),
 *   parentId = "payment_failed"
 * )
 */
class AuthorizationFailed extends Base {
}
