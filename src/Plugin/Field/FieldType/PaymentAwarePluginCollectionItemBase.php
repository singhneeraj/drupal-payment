<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PaymentAwarePluginBagItemBase.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\plugin\Plugin\Field\FieldType\PluginCollectionItemBase;

/**
 * Provides a plugin bag for payment-aware plugins.
 */
abstract class PaymentAwarePluginCollectionItemBase extends PluginCollectionItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['plugin_instance'] = MapDataDefinition::create('payment_aware_plugin_instance')
      ->setLabel(t('Plugin instance'));

    return $properties;
  }

}
