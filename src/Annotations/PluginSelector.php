<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\PluginSelector.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a plugin selector plugin annotation.
 *
 * @Annotation
 */
class PluginSelector extends Plugin {

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
