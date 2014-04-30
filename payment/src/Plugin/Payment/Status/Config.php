<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Config.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * A status based on a configuration entity.
 *
 * @PaymentStatus(
 *   operations_provider = "\Drupal\payment\Plugin\Payment\Status\ConfigOperationsProvider",
 *   derivative = "\Drupal\payment\Plugin\Payment\Status\ConfigDerivative",
 *   id = "payment_config",
 *   label = @Translation("Configuration entity status")
 * )
 */
class Config extends PaymentStatusBase {
}
