<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentLineItemWebTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Generate;
use Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment_line_item element.
 */
class PaymentLineItemWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'payment_line_item element',
      'group' => 'Payment',
    );
  }

  /**
   * Creates line item form data.
   *
   * @param array $names
   *   Line item machine names.
   *
   * @return array
   */
  protected function lineItemData(array $names) {
    $data = array();
    foreach ($names as $name) {
      $data += array(
        'line_item[line_items][' . $name . '][plugin_form][amount][amount]' => '10.0',
        'line_item[line_items][' . $name . '][plugin_form][description]' => 'foo',
        'line_item[line_items][' . $name . '][plugin_form][quantity]' => '1',
      );
    }

    return $data;
  }

  /**
   * Asserts the presence of the element's line item elements.
   */
  protected function assertLineItemElements(array $names) {
    foreach (array_keys($this->lineItemData($names)) as $input_name) {
      $this->assertFieldByName($input_name);
    }
  }

  /**
   * Asserts the presence of the element's add more elements..
   */
  protected function assertAddMore($present) {
    $elements = $this->xpath('//select[@name="line_item[add_more][type]"]');
    $this->assertEqual($present, isset($elements[0]));
    $elements = $this->xpath('//input[@id="edit-line-item-add-more-add"]');
    $this->assertEqual($present, isset($elements[0]));
  }

  /**
   * Tests the element.
   *
   * @todo Test deleting a line item once WebTestBase::drupalPostAjax() supports
   *   testing RemoveCommand.
   */
  protected function testElement() {
    $state = $this->container->get('state');
    $names = array();
    foreach (Generate::createPaymentLineItems() as $line_item) {
      $names[] = $line_item->getName();
    }
    $type = 'payment_basic';

    // Test the presence of default elements.
    $this->drupalGet('payment_test-element-payment-line-item');
    $this->assertLineItemElements($names);
    $this->assertAddMore(TRUE);

    // Add a line item through a regular submission.
    $this->drupalPost(NULL, array(
      'line_item[add_more][type]' => $type,
    ), t('Add a line item'));
    $this->assertLineItemElements(array_merge($names, array($type)));
    $this->assertAddMore(FALSE);

    // Delete a line item through a regular submission.
    $this->drupalPost(NULL, array(), t('Delete'));
    $this->assertLineItemElements($names);
    $elements = $this->xpath('//input[@name="line_item[line_items][' . $type . '][weight]"]');
    $this->assertFalse(isset($elements[0]));
    $this->assertAddMore(TRUE);

    // Change a line item's weight and test the element's value through a
    // regular submission.
    $name = 'line_item[line_items][' . reset($names) . '][weight]';
    $this->assertFieldByXPath('//select[@name="' . $name . '"]/option[@value="0" and @selected="selected"]');
    $this->drupalPost(NULL, array(
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
    $this->drupalPostAJAX('payment_test-element-payment-line-item', array(
      'line_item[add_more][type]' => $type,
    ), array(
      'op' => t('Add a line item'),
    ));
    $this->assertLineItemElements(array_merge($names, array($type)));
    $this->assertAddMore(FALSE);

    // Test the element's value through an AJAX submission.
    $this->drupalPost(NULL, array(
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
