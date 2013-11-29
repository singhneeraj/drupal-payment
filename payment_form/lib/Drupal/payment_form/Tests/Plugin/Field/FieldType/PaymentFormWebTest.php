<?php

/**
 * @file
 * Contains class \Drupal\payment_form\Tests\Plugin\Field\FieldType\PaymentFormWebTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\FieldInterface;
use Drupal\payment\Generate;
use Drupal\payment\Payment;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment_form\Plugin\field\field_type\PaymentForm.
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_form');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment_form\Plugin\Field\FieldType\PaymentForm web test',
      'group' => 'Payment Form Field',
    );
  }

  /**
   * Tests the field.
   */
  protected function testField() {
    // Create the field and field instance.
    $field_name = strtolower($this->randomName());
    entity_create('field_entity', array(
      'cardinality' => FieldInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $field_name,
      'type' => 'payment_form',
    ))->save();

    entity_create('field_instance', array(
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
