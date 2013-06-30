<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\PaymentStatus.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a payment method plugin annotation.
 *
 * @Annotation
 */
class PaymentStatus extends Plugin {

  /**
   * The translated human-readable plugin name (optional).
   *
   * @var string
   */
  public $description = '';

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

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $parentID;
}
