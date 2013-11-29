<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentUiWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Generate;
use Drupal\payment\Payment;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment UI.
 */
class PaymentUiWebTest extends WebTestBase {

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
    $payment_method = Generate::createPaymentMethod(2, Payment::methodManager()->createInstance('payment_test'));
    $payment_method->save();
    $payment = Generate::createPayment(2, $payment_method);
    $payment->save();
    $payment = entity_load_unchanged('payment', $payment->id());

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
    }

    // Update the payment.
    $path = 'payment/' . $payment->id() . '/edit';
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.update.any')));
    $this->drupalGet($path);
    if ($this->assertResponse('200')) {
      $this->assertFieldByXPath('//select[@name="status_plugin_id"]');
      $this->drupalPostForm(NULL, array(
        'status_plugin_id' => 'payment_cancelled',
      ), t('Save'));
    }
    $this->assertUrl('payment/' . $payment->id());
    $payment = entity_load_unchanged('payment', $payment->id());
    $this->assertEqual($payment->getStatus()->getPluginId(), 'payment_cancelled');

    // Delete a payment.
    $path = 'payment/' . $payment->id() . '/delete';
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.delete.any')));
    $this->drupalGet($path);
    if ($this->assertResponse('200')) {
      $this->clickLink(t('Cancel'));
      $this->assertUrl('payment/' . $payment->id());
      $this->drupalGet($path);
      $this->drupalPostForm(NULL, array(), t('Delete'));
      $this->assertResponse('200');
      $this->assertFalse((bool) \Drupal::entityManager()->getStorageController('payment')->loadUnchanged($payment->id()));
    }
  }
}
