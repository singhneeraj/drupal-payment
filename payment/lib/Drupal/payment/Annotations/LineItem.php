<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\LineItem.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a payment line item plugin annotation.
 *
 * @Annotation
 */
class LineItem extends Plugin {

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
