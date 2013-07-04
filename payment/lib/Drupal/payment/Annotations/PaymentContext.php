<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\PaymentContext.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a payment context plugin annotation.
 *
 * @Annotation
 */
class PaymentContext extends Plugin {

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
