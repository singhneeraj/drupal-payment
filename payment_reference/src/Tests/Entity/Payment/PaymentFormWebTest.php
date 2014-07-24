<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Entity\Payment\PaymentFormWebTest.
 */

namespace Drupal\payment_reference\Tests\Entity\Payment;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\payment\Tests\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment_reference\Entity\Payment\PaymentForm web test.
 *
 * @group Payment Reference Field
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('filter', 'payment', 'payment_reference', 'payment_reference_test');

  /**
   * Tests the form.
   */
  protected function testForm() {
    // Create a user.
    $user = $this->drupalCreateUser(array('administer users'));
    $this->drupalLogin($user);

    // Create a payment method.
    $payment_method = \Drupal\payment\Tests\Generate::createPaymentMethodConfiguration(2, 'payment_basic');
    $payment_method->setPluginConfiguration(array(
      'status' => 'payment_success',
    ));
    $payment_method->save();

    // Create the field and field instance.
    $field_name = strtolower($this->randomName());
    entity_create('field_storage_config', array(
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $field_name,
      'type' => 'payment_reference',
    ))->save();

    /** @var \Drupal\field\FieldInstanceConfigInterface $field_instance_config */
    $field_instance_config = entity_create('field_instance_config', array(
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $field_name,
      'settings' => array(
        'currency_code' => 'EUR',
        'line_items_data' => array(),
      ),
    ));
    $field_instance_config->save();

    $path = '/payment_reference/pay/user/user/' . $field_name;
    $this->drupalGet($path);
    $this->drupalPostForm($path, array(), t('Pay'));
    // This actually tests the payment_reference payment type plugin, but it lets
    $this->assertUrl('payment_reference/resume/1');
  }
}
