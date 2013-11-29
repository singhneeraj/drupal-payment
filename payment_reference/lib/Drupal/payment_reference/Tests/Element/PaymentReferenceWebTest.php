<?php

/**
 * @file
 * Contains class \Drupal\payment_reference\Tests\Element\PaymentReferenceWebTest.
 */

namespace Drupal\payment_reference\Tests\Element;

use Drupal\payment\Generate;
use Drupal\payment_reference\PaymentReference;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the payment_reference element.
 */
class PaymentReferenceWebTest extends WebTestBase {

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
      'name' => 'payment_reference element web test',
      'group' => 'Payment Reference Field',
    );
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $state = \Drupal::state();
    $path = 'payment_reference_test-element-payment_reference';

    // Test without queued payments.
    $this->drupalGet($path);
    $this->assertLinkByHref('payment_reference/pay/payment_reference_test_payment_reference_element');
    $this->drupalPostForm($path, array(), t('Submit'));
    $this->assertText('Foo field is required');
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertNull($value);

    // Test with a queued payment.
    $payment_method = Generate::createPaymentMethod(2);
    $payment_method->save();
    $payment = Generate::createPayment(2, $payment_method);
    $payment->setStatus(\Drupal::service('plugin.manager.payment.status')->createInstance('payment_success'));
    $payment->save();
    PaymentReference::queue()->save('payment_reference_test_payment_reference_element', $payment->id());
    $this->drupalGet($path);
    $this->drupalPostForm($path, array(), t('Submit'));
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertEqual($value, $payment->id());





    return;
    $this->assertLineItemElements($names);
    $this->assertAddMore(TRUE);

    // Add a line item through a regular submission.
    $this->drupalPostForm(NULL, array(
      'line_item[add_more][type]' => $type,
    ), t('Add a line item'));
    $this->assertLineItemElements(array_merge($names, array($type)));
    $this->assertAddMore(FALSE);

    // Delete a line item through a regular submission.
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertLineItemElements($names);
    $elements = $this->xpath('//input[@name="line_item[line_items][' . $type . '][weight]"]');
    $this->assertFalse(isset($elements[0]));
    $this->assertAddMore(TRUE);

    // Change a line item's weight and test the element's value through a
    // regular submission.
    $name = 'line_item[line_items][' . reset($names) . '][weight]';
    $this->assertFieldByXPath('//select[@name="' . $name . '"]/option[@value="0" and @selected="selected"]');
    $this->drupalPostForm(NULL, array(
      // Change the first line item's weight to be the highest.
      $name => count($names),
    ), t('Submit'));
    $value = $state->get('payment_test_line_item_form_element');
    if ($this->assertTrue(is_array($value))) {
      /// We end up with one more line item than we originally had.
      $this->assertEqual(count($value), count($names));
      foreach ($value as $line_item) {
        $this->assertTrue($line_item instanceof PaymentLineItemInterface);
      }
      // Check that the first line item is now the last.
      $this->assertEqual(end($value)->getName(), reset($names));
    }

    // Add a line item through an AJAX submission.
    $this->drupalPostAjaxForm('payment_test-element-payment-line-item', array(
      'line_item[add_more][type]' => $type,
    ), array(
      'op' => t('Add a line item'),
    ));
    $this->assertLineItemElements(array_merge($names, array($type)));
    $this->assertAddMore(FALSE);

    // Test the element's value through an AJAX submission.
    $this->drupalPostForm(NULL, array(
      'line_item[line_items][payment_basic][plugin_form][description]' => $this->randomString(),
    ), t('Submit'));
    $value = $state->get('payment_test_line_item_form_element');
    if ($this->assertTrue(is_array($value))) {
      $this->assertEqual(count($value), count($names) + 1);
      foreach ($value as $line_item) {
        $this->assertTrue($line_item instanceof PaymentLineItemInterface);
      }
    }
  }
}
