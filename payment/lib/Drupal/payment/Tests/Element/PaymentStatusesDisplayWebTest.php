<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentStatusesDisplayWebTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the payment_statuses_display element.
 */
class PaymentStatusesDisplayWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'payment_statuses_display element',
      'group' => 'Payment',
    );
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $payment = Generate::createPayment(2)
      ->setStatus($this->container->get('plugin.manager.payment.status')->createInstance('payment_failed'));
    $element = array(
      '#statuses' => $payment->getStatuses(),
      '#type' => 'payment_statuses_display',
    );
    $this->drupalSetContent(drupal_render($element));
    $this->verbose($this->drupalGetContent());
    $strings = array('<table', t('Status'), t('Date'), t('Created'), t('Failed'), 'payment-status-plugin-payment_created');
    foreach ($strings as $string) {
      $this->assertRaw($string);
    }
  }
}
