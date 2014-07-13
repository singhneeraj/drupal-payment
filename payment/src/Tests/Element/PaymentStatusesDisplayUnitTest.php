<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\PaymentStatusesDisplayUnitTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Tests\Generate;
use Drupal\payment\Payment;
use Drupal\simpletest\KernelTestBase;

/**
 * payment_statuses_display element unit test.
 *
 * @group Payment
 */
class PaymentStatusesDisplayUnitTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('currency', 'field', 'payment', 'system', 'user');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(array('system'));
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $status = Payment::statusManager()->createInstance('payment_failed');
    $status->setCreated(time());
    $payment = Generate::createPayment(2)
      ->setStatus($status);
    $element = array(
      '#statuses' => $payment->getStatuses(),
      '#type' => 'payment_statuses_display',
    );
    $output = drupal_render($element);
    $strings = array('<table', t('Status'), t('Date'), t('Created'), t('Failed'), 'payment-status-plugin-payment_created');
    foreach ($strings as $string) {
      $this->assertNotIdentical(strpos($output, $string), FALSE);
    }
  }
}
