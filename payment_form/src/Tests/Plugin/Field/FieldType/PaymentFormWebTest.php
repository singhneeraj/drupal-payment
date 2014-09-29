<?php

/**
 * @file
 * Contains \Drupal\payment_form\Tests\Plugin\Field\FieldType\PaymentFormWebTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\payment\Tests\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm.
 *
 * @group Payment Form Field
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_form');

  /**
   * Tests the field.
   */
  protected function testField() {
    // Create the field and field instance.
    $field_name = strtolower($this->randomMachineName());
    entity_create('field_storage_config', array(
      'cardinality' => FieldStorageConfigInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'field_name' => $field_name,
      'type' => 'payment_form',
    ))->save();

    entity_create('field_config', array(
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $field_name,
      'settings' => array(
        'currency_code' => 'EUR',
      ),
    ))->save();

    // Set a field value on an entity and test getting it.
    $user = entity_create('user', array(
      'name' => $this->randomString(),
    ));
    foreach (Generate::createPaymentLineItems() as $i => $line_item) {
      $user->{$field_name}[$i]->plugin_id = $line_item->getPluginId();
      $user->{$field_name}[$i]->plugin_configuration = $line_item->getConfiguration();
    }
    $this->assertFieldValue($user, $field_name);

    // Save the entity, load it from storage and test getting the field value.
    $user->save();
    $user = entity_load_unchanged('user', $user->id());
    $this->assertFieldValue($user, $field_name);
  }

  /**
   * Asserts a correct field value.
   */
  protected function assertFieldValue(EntityInterface $entity, $field_name) {
    $field = $entity->{$field_name};
    foreach (Generate::createPaymentLineItems() as $i => $line_item) {
      $this->assertTrue(is_string($field[$i]->plugin_id));
      $this->assertTrue(is_array($field[$i]->plugin_configuration));
    }
  }
}
