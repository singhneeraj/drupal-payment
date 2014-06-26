<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Plugin\Field\FieldWidget\PaymentReferenceWebTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment_reference\Plugin\field\widget\PaymentReference.
 */
class PaymentReferenceWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_reference');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference web test',
      'group' => 'Payment Reference Field',
    );
  }

  /**
   * Tests the widget.
   */
  protected function testWidget() {
    // Create the field and field instance.
    $field_name = strtolower($this->randomName());
    entity_create('field_config', array(
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
        'line_items' => array(),
      ),
    ))->save();

    entity_get_form_display('user', 'user', 'default')
      ->setComponent($field_name, array())
      ->save();

    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);

    // Test the widget when creating an entity.
    $this->drupalGet('user/' . $user->id() . '/edit');
    $this->clickLink(t('Add a new payment'));
    $this->assertUrl('/payment_reference/pay/user.user.' . $field_name);
    $this->assertResponse('200');
  }
}
