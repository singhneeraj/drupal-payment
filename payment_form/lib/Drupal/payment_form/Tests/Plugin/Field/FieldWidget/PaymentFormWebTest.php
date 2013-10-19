<?php

/**
 * @file
 * Contains class \Drupal\payment_form\Tests\Plugin\Field\FieldWidget\PaymentFormWebTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldWidget;

use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment_form\Plugin\field\widget\PaymentForm.
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field_ui', 'payment', 'payment_form');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm web test',
      'group' => 'Payment Form Field',
    );
  }

  /**
   * Tests the widget.
   */
  protected function testWidget() {
    $user = $this->drupalCreateUser(array('administer user fields'));
    $this->drupalLogin($user);

    // Test the widget when setting a default field value.
    $field_name = strtolower($this->randomName());
    $this->drupalPostForm('admin/config/people/accounts/fields', array(
      'fields[_add_new_field][label]' => $this->randomString(),
      'fields[_add_new_field][field_name]' => $field_name,
      'fields[_add_new_field][type]' => 'payment_form',
    ), t('Save'));
    $this->drupalPostForm(NULL, array(), t('Save field settings'));
    $this->drupalPostForm(NULL, array(), t('Add a line item'));
    $this->drupalPostForm(NULL, array(
      'default_value_input[field_' . $field_name . '][line_items][line_items][payment_basic][plugin_form][description]' => $this->randomString(),
    ), t('Save settings'));
    // Get all payment_form fields.
    $field_names = $this->container->get('entity.query')->get('field_entity')
      ->condition('type', 'payment_form')
      ->execute();
    $this->assertTrue(in_array('user.field_' . $field_name, $field_names));

    // Test the widget when creating an entity.
    $this->drupalGet('user/' . $user->id() . '/edit');
    $this->drupalPostForm(NULL, array(
      'field_' . $field_name . '[line_items][add_more][type]' => 'payment_basic',
    ), t('Add a line item'));
    $description = $this->randomString();
    $this->drupalPostForm(NULL, array(
      'field_' . $field_name . '[line_items][line_items][payment_basic][plugin_form][description]' => $description,
    ), t('Save'));
    drupal_flush_all_caches();
    $this->container->get('field.info')->flush();
    $user_storage = $this->container->get('plugin.manager.entity')->getStorageController('user');
    $user = $user_storage->loadUnchanged($user->id());
    $line_item = $user->{'field_' . $field_name}[0]->line_item;
    $this->assertEqual($line_item->getDescription(), $description);
  }
}
