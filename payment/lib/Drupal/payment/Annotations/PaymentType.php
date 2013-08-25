<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\PaymentType.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a payment type plugin annotation.
 *
 * @Annotation
 */
class PaymentType extends Plugin {

  /**
   * The translated human-readable plugin description (optional).
   *
   * @var string
   */
  public $description;

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
