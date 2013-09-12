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
  // @todo Remove the dependency on Node once https://drupal.org/node/2085571
  //   has been fixed.
  public static $modules = array('node', 'payment', 'payment_test');

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
   * Tests the payment UI.
   */
  protected function testPaymentUI() {
    $payment_method = Generate::createPaymentMethod(2, $this->container->get('plugin.manager.payment.method')->createInstance('payment_test'));
    $payment_method->save();
    $payment = Generate::createPayment(2, $payment_method);
    $payment->save();

    // View the administrative listing.
    $this->drupalLogin($this->drupalCreateUser(array('access content overview')));
    $this->drupalGet('admin/content');
    $this->assertResponse('200');
    $this->assertNoLinkByHref('admin/content/payment');
    $this->drupalGet('admin/content/payment');
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('access content overview', 'payment.payment.view.any')));
    $this->drupalGet('admin/content');
    $this->clickLink(t('Payments'));
    if ($this->assertResponse('200')) {
      $this->assertTitle(t('Payments | Drupal'));
      $this->assertText(t('Last updated'));
      $this->assertText(t('Payment method'));
      $this->assertText(t('€24.20'));
      $this->assertText($payment_method->label());
      $this->assertLinkByHref('payment/1/operation/foo_bar');
      $this->assertNoLinkByHref('payment/1/operation/access_denied');
    }
    $this->drupalLogout();

    // View the payment.
    $path = 'payment/' . $payment->id();
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.view.any')));
    $this->drupalGet($path);
    if ($this->assertResponse('200')) {
      $this->assertText(t('Payment method'));
      $this->assertText(t('Status'));
      $this->assertLinkByHref('payment/1/operation/foo_bar');
      $this->assertNoLinkByHref('payment/1/operation/access_denied');
    }

    // Perform a payment operation.
    $this->clickLink('FooBarOperation');
    $this->assertResponse('200');
    $this->assertEqual($this->container->get('state')->get('payment_test_execute_operation'), 'foo_bar');

    // Delete a payment.
    $path = 'payment/' . $payment->id() . '/delete';
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.delete.any')));
    $this->drupalGet($path);
    if ($this->assertResponse('200')) {
      $this->drupalPostForm(NULL, array(), t('Delete'));
      $this->assertResponse('200');
      $this->assertFalse((bool) $this->container->get('plugin.manager.entity')->getStorageController('payment')->loadUnchanged($payment->id()));
    }
  }
}
