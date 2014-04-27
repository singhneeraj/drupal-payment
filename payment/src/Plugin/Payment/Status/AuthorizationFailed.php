<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\AuthorizationFailed.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * A payment that failed authorization.
 *
 * @PaymentStatus(
 *   id = "payment_authorization_failed",
 *   label = @Translation("Authorization failed"),
 *   parent_id = "payment_failed"
 * )
 */
class AuthorizationFailed extends PaymentStatusBase {
}
