<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Entity\PaymentFormControllerWebTest.
 */

namespace Drupal\payment_reference\Tests\Entity;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment_reference\Entity\PaymentFormController.
 */
class PaymentFormControllerWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_reference', 'payment_reference_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment_reference\Entity\PaymentFormController web test',
      'group' => 'Payment Reference Field',
    );
  }

  /**
   * Tests the form.
   */
  protected function testForm() {
    // Create a payment method.
    $payment_method = Generate::createPaymentMethod(2, 'payment_basic');
    $payment_method->setPluginConfiguration(array(
      'status' => 'payment_success',
    ));
    $payment_method->save();

    // Create the field and field instance.
    $field_name = strtolower($this->randomName());
    entity_create('field_config', array(
      'cardinality' => FieldDefinitionInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $field_name,
      'type' => 'payment_reference',
    ))->save();

    $field_instance_config = entity_create('field_instance_config', array(
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $field_name,
      'settings' => array(
        'currency_code' => 'EUR',
        'line_items' => array(),
      ),
    ));
    $field_instance_config->save();

    $path = '/payment_reference/pay/' . $field_instance_config->id();
    $this->drupalGet($path);
    $this->drupalPostForm($path, array(), t('Pay'));
    // This actually tests the payment_reference payment type plugin, but it lets
    $this->assertUrl('payment_reference/resume/1');
  }
}
