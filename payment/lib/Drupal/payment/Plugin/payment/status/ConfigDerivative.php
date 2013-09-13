<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\payment\status\ConfigDerivative.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Component\Plugin\Derivative\DerivativeBase;

/**
 * Retrieves payment status plugin definitions based on configuration entities.
 */
class ConfigDerivative extends DerivativeBase {
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    $statuses = entity_load_multiple('payment_status');
    foreach ($statuses as $status) {
      $this->derivatives[$status->id] = array(
          'description' => $status->getDescription(),
          'label' => $status->label(),
          'parent_id' => $status->getParentId(),
        ) + $base_plugin_definition;
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }
}
