<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PaymentStatusItem.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\payment\Payment;

/**
 * Provides a plugin collection for payment type plugins.
 *
 * @FieldType(
 *   id = "payment_status",
 *   label = @Translation("Payment status plugins")
 * )
 */
class PaymentStatusItem extends PaymentAwarePluginCollectionItemBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginManager() {
    // @todo Unit-test this.
    return Payment::statusManager();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['created'] = DataDefinition::create('payment_status_created')
      ->setLabel(t('Created'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['created'] = [
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'unsigned' => TRUE,
    ];

    return $schema;
  }

}
