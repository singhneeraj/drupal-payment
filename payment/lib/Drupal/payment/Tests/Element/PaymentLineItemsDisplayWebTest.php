<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentLineItemsDisplayWebTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the payment_line_items_display element.
 */
class PaymentLineItemsDisplayWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('currency', 'payment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'payment_line_items_display element',
      'group' => 'Payment',
    );
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $element = array(
      '#payment' => Generate::createPayment(2),
      '#type' => 'payment_line_items_display',
    );
    $this->drupalSetContent(drupal_render($element));
    $this->verbose($this->drupalGetContent());
    $strings = array('<table', t('Total amount'), t('Quantity'), 'payment-line-item-name-foo', 'payment-line-item-plugin-payment_basic', 'ƒ9.90');
    foreach ($strings as $string) {
      $this->assertRaw($string);
    }
  }
}
