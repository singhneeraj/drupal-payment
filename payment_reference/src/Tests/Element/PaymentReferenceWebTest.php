<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Element\PaymentReferenceWebTest.
 */

namespace Drupal\payment_reference\Tests\Element;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\payment\Entity\Payment;
use Drupal\payment\Entity\PaymentInterface;
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
  public static $modules = array('payment', 'payment_reference', 'payment_reference_test', 'payment_test', 'text');

  /**
   * Tests the element.
   */
  protected function testElement() {
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);

    // Create the payment reference field and field instance.
    $payment_reference_field_name = 'foobarbaz';
    entity_create('field_storage_config', array(
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $payment_reference_field_name,
      'type' => 'payment_reference',
    ))->save();
    entity_create('field_instance_config', array(
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $payment_reference_field_name,
      'settings' => array(
        'currency_code' => 'EUR',
        'line_items_data' => array(),
      ),
    ))->save();

    // Create a field on the payment entity type.
    $payment_field_name = 'quxfoobar';
    entity_create('field_storage_config', array(
      'cardinality' => 1,
      'entity_type' => 'payment',
      'name' => $payment_field_name,
      'type' => 'text',
    ))->save();
    entity_create('field_instance_config', array(
      'bundle' => 'payment_reference',
      'entity_type' => 'payment',
      'field_name' => $payment_field_name,
      'required' => TRUE,
    ))->save();
    entity_get_form_display('payment', 'payment_reference', 'default')
      ->setComponent($payment_field_name, array(
        'type' => 'text_textfield',
      ))
      ->save();

    $state = \Drupal::state();
    $path = 'payment_reference_test-element-payment_reference';

    // Test without selecting a payment method.
    $this->drupalGet($path);
    $this->drupalPostForm(NULL, array(), t('Pay'));
    $this->assertText('Payment method field is required');
    $this->assertText('quxfoobar field is required');
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertNull($value);

    // Test with a non-interruptive payment method.
    $text_field_value = $this->randomMachineName();
    $this->drupalPostForm($path, array(
      'payment_reference[container][payment_form][payment_method][container][select][payment_method_id]' => 'payment_test_uninterruptive',
      'payment_reference[container][payment_form][quxfoobar][0][value]' => $text_field_value,
    ), t('Choose payment method'));
    $this->drupalPostForm(NULL, array(), t('Pay'));
    $this->drupalPostForm(NULL, array(), t('Submit'));
    $payment_id = $state->get('payment_reference_test_payment_reference_element');
    $this->assertTrue(is_int($payment_id));
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = Payment::load($payment_id);
    $this->assertTrue($payment instanceof PaymentInterface);
    $this->assertEqual($payment->get('quxfoobar')[0]->get('value')->getValue(), $text_field_value);

    // Test with an interruptive payment method.
    $text_field_value = $this->randomMachineName();
    // @todo Once Behat is supported, test the behavior of opening a new window
    //   and going back to the original form.
    $this->drupalPostForm($path, array(
      'payment_reference[container][payment_form][payment_method][container][select][payment_method_id]' => 'payment_test_interruptive',
      'payment_reference[container][payment_form][quxfoobar][0][value]' => $text_field_value,
    ), t('Choose payment method'));
    $this->drupalPostForm(NULL, array(), t('Pay'));
    $this->clickLink(t('Complete payment'));
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = Payment::loadMultiple()[2];
    $this->assertEqual($payment->getPaymentStatus()->getPluginId(), 'payment_success');
    $this->assertEqual($payment->get('quxfoobar')[0]->get('value')->getValue(), $text_field_value);
  }

}
