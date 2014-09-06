<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentStorageSchema.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Schema\SqlContentEntityStorageSchema;

/**
 * Provides a payment storage schema handler.
 */
class PaymentStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    $this->alterEntitySchemaWithNonFieldColumns($schema);

    return $schema;
  }

  /**
   *
   */
  protected function alterEntitySchemaWithNonFieldColumns(array &$schema) {
    $schema['payment']['fields'] += array(
      'first_payment_status_id' => array(
        'description' => "The {payment_status_item}.id of this payment's first status item.",
        'type' => 'int',
        'unsigned' => TRUE,
        'default' => 0,
        'not null' => TRUE,
      ),
      'last_payment_status_id' => array(
        'description' => "The {payment_status_item}.id of this payment's most recent status item.",
        'type' => 'int',
        'unsigned' => TRUE,
        'default' => 0,
        'not null' => TRUE,
      ),
      'payment_method_configuration' => array(
        'type' => 'blob',
        'not null' => TRUE,
        'serialize' => TRUE,
      ),
      'payment_method_id' => array(
        'type' => 'varchar',
        'length' => 255,
      ),
      'payment_type_configuration' => array(
        'type' => 'blob',
        'not null' => TRUE,
        'serialize' => TRUE,
      ),
      'payment_type_id' => array(
        'type' => 'varchar',
        'length' => 255,
      ),
    );
    $schema['payment']['foreign keys'] += array(
      'first_payment_status_id' => array(
        'table' => 'payment_status_item',
        'columns' => array(
          'first_payment_status_id' => 'id',
        ),
      ),
      'last_payment_status_id' => array(
        'table' => 'payment_status_item',
        'columns' => array(
          'last_payment_status_id' => 'id',
        ),
      ),
      'owner_id' => array(
        'table' => 'user',
        'columns' => array(
          'owner_id' => 'uid',
        ),
      ),
    );

    return $schema;
  }

}
