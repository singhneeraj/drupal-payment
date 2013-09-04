<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentUIWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment UI.
 */
class PaymentUIWebTest extends WebTestBase {

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
      'name' => 'Payment UI',
      'group' => 'Payment',
    );
  }

  /**
   * Tests viewing and deleting a payment.
   */
  protected function testPaymentUI() {
    $payment_method = Generate::createPaymentMethod(2, $this->container->get('plugin.manager.payment.method')->createInstance('payment_test'));
    $payment_method->save();
    $payment = Generate::createPayment(2, $payment_method);
    $payment->save();

    // View the payment.
    $path = 'payment/' . $payment->id();
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.view.any')));
    $this->drupalGet($path);
    $this->assertResponse('200');
    $this->assertText(t('Payment method'));
    $this->assertText(t('Status'));
    $this->assertLinkByHref('payment/1/operation/foo');
    $this->assertNoLinkByHref('payment/1/operation/access_denied');

    // Perform a payment operation.
    $this->clickLink('Foo');
    $this->assertResponse('200');
    $this->assertEqual($this->container->get('state')->get('payment_test_execute_operation'), 'foo');

    // Delete a payment.
    $path = 'payment/' . $payment->id() . '/delete';
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.delete.any')));
    $this->drupalGet($path);
    $this->assertResponse('200');
    $this->drupalPost(NULL, array(), t('Delete'));
    $this->assertResponse('200');
    $this->assertFalse((bool) $this->container->get('plugin.manager.entity')->getStorageController('payment')->loadUnchanged($payment->id()));
  }
}
