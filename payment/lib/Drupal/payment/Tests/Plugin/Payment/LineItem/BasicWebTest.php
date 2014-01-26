<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Payment\LineItem\BasicWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\LineItem;

use Drupal\payment\Payment;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment\Plugin\Payment\LineItem\Basic.
 */
class BasicWebTest extends WebTestBase {

  /**
   * The line item to test.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\Basic
   */
  protected $lineItem;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\LineItem\Basic web test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
 protected function setUp() {
    parent::setUp();
    $this->lineItem = Payment::lineItemManager()->createInstance('payment_basic');
  }

  /**
   * Tests formElements().
   */
  protected function testFormElements() {
    $line_item_data = array(
      'line_item[amount][amount]' => '123.45',
      'line_item[quantity]' => '3',
      'line_item[description]' => 'Foo & Bar',
    );

    // Tests the presence of child form elements and their default values.
    $this->drupalGet('payment_test-plugin-payment-line_item-payment_basic');
    foreach (array_keys($line_item_data) as $name) {
      $this->assertFieldByName($name);
    }

    // Test valid values.
    $data = $line_item_data;
    $data['line_item[description]'] = 'FooBar';
    $this->drupalPostForm('payment_test-plugin-payment-line_item-payment_basic', $data, t('Submit'));
    $this->assertUrl('user', array(), 'Valid values trigger form submission.');

    // Test a non-integer quantity.
    $values =  array(
      'line_item[quantity]' => $this->randomName(2),
    );
    $this->drupalPostForm('payment_test-plugin-payment-line_item-payment_basic', $values, t('Submit'));
    $this->assertFieldByXPath('//input[@name="line_item[quantity]" and contains(@class, "error")]');
  }
}
