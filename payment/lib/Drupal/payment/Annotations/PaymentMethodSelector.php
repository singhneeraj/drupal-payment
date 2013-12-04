<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\PaymentMethodSelector.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a payment method selector plugin annotation.
 *
 * @Annotation
 */
class PaymentMethodSelector extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The translated human-readable plugin name.
   *
   * @var string
   */
  public $label;
}
