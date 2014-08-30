<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Element\PaymentReferenceWebTest.
 */

namespace Drupal\payment_reference\Tests\Element;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\payment\Tests\Generate;
use Drupal\payment_reference\PaymentReference;
use Drupal\simpletest\WebTestBase;

/**
 * payment_reference element web test.
 *
 * @group Payment Reference Field
 */
class PaymentReferenceWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_reference', 'payment_reference_test');

  /**
   * Tests the element.
   */
  protected function testElement() {
    // Create the field and field instance.
    $field_name = 'foobarbaz';
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

    $state = \Drupal::state();
    $path = 'payment_reference_test-element-payment_reference';

    // Test without queued payments.
    $this->drupalGet($path);
    $this->assertLinkByHref('payment_reference/pay/user/user/' . $field_name);
    $this->drupalPostForm($path, array(), t('Submit'));
    $this->assertText('FooBarBaz field is required');
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertNull($value);

    // Test with a queued payment.
    $payment = Generate::createPayment(2);
    $payment->setPaymentStatus(\Drupal::service('plugin.manager.payment.status')->createInstance('payment_success'));
    $payment->save();
    PaymentReference::queue()->save('user.user.' . $field_name, $payment->id());
    $this->drupalGet($path);
    $this->drupalPostForm($path, array(), t('Submit'));
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertEqual($value, $payment->id());
  }
}
