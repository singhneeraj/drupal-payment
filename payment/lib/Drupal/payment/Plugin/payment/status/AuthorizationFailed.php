<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\AuthorizationFailed.
 */

namespace Drupal\payment\Plugin\payment\status;

/**
 * A payment that failed authorization.
 *
 * @PaymentStatus(
 *   id = "payment_authorization_failed",
 *   label = @Translation("Authorization failed"),
 *   parent_id = "payment_failed"
 * )
 */
class AuthorizationFailed extends Base {
}
