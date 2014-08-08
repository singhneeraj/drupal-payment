<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Plugin\Field\FieldType\PaymentReferenceWebTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\payment_reference\PaymentReference;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference web test.
 *
 * @group Payment Reference Field
 */
class PaymentReferenceWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field_ui', 'payment', 'payment_reference');

  /**
   * Tests the field.
   */
  protected function testField() {
    // Create the field and field instance.
    $field_name = strtolower($this->randomMachineName());
    entity_create('field_storage_config', array(
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $field_name,
      'type' => 'payment_reference',
    ))->save();

    entity_create('field_instance_config', array(
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $field_name,
      'settings' => array(
        'currency_code' => 'EUR',
        'line_items_data' => array(),
      ),
    ))->save();

    $payment = entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    ));
    $payment->save();
    PaymentReference::queue()->save('user.' . $field_name, $payment->id());

    // Set a field value on an entity and test getting it.
    $user = entity_create('user', array(
      'name' => $this->randomString(),
    ));

    $user->{$field_name}[0]->target_id = $payment->id();
    $this->assertEqual($user->{$field_name}[0]->entity->id(), $payment->id());

    // Save the entity, load it from storage and test getting the field value.
    $user->save();
    $user = entity_load_unchanged('user', $user->id());
    $this->assertEqual($user->{$field_name}[0]->target_id, $payment->id());
  }

  /**
   * Tests creating the field through the administrative user interface.
   */
  protected function testFieldCreation() {
    $field_id = strtolower($this->randomMachineName());
    $field_label = $this->randomMachineName();
    $description = $this->randomMachineName();
    $quantity = mt_rand();
    $currency_code = 'EUR';
    $amount = '12.34';
    $user = $this->drupalCreateUser(array('administer user fields'));
    $this->drupalLogin($user);
    $this->drupalPostForm('admin/config/people/accounts/fields', array(
      'fields[_add_new_field][label]' => $field_label,
      'fields[_add_new_field][field_name]' => $field_id,
      'fields[_add_new_field][type]' => 'payment_reference',
    ), t('Save'));
    $this->drupalPostForm(NULL, array(), t('Save field settings'));
    $this->drupalPostForm(NULL, array(
      'instance[settings][line_items][add_more][type]' => 'payment_basic',
    ), t('Add a line item'));
    $this->drupalPostForm(NULL, array(
      'instance[settings][currency_code]' => $currency_code,
      'instance[settings][line_items][line_items][payment_basic][plugin_form][amount][amount]' => $amount,
      'instance[settings][line_items][line_items][payment_basic][plugin_form][amount][currency_code]' => $currency_code,
      'instance[settings][line_items][line_items][payment_basic][plugin_form][description]' => $description,
      'instance[settings][line_items][line_items][payment_basic][plugin_form][quantity]' => $quantity,
    ), t('Save settings'));
    $this->assertResponse(200);

    // Re-load the page and test that the values are picked up.
    $this->drupalGet('admin/config/people/accounts/fields/user.user.field_' . $field_id);
    $this->assertFieldByName('instance[settings][line_items][line_items][payment_basic][plugin_form][amount][currency_code]', $currency_code);
    $this->assertFieldByName('instance[settings][line_items][line_items][payment_basic][plugin_form][amount][amount]', $amount);
    $this->assertFieldByName('instance[settings][line_items][line_items][payment_basic][plugin_form][description]', $description);
    $this->assertFieldByName('instance[settings][line_items][line_items][payment_basic][plugin_form][quantity]', $quantity);
  }
}
