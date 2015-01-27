<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\field\formatter\PluginLabel.
 */

namespace Drupal\payment\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * A payment plugin bag field formatter.
 *
 * @FieldFormatter(
 *   id = "payment_plugin_label",
 *   label = @Translation("Label"),
 *   field_types = {
 *     "payment_method",
 *     "payment_type",
 *   }
 * )
 */
class PluginLabel extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $build = [];
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $item */
    foreach ($items as $delta => $item) {
      $plugin_definition = $item->getContainedPluginInstance()->getPluginDefinition();
      $build[$delta] = [
        '#markup' => $plugin_definition['label'],
      ];
    }

    return $build;
  }

}
