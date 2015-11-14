<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Controller\PaymentWebTest.
 */

namespace Drupal\payment\Tests\Controller;

use Drupal\payment\Payment;
use Drupal\payment\Tests\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * Payment UI.
 *
 * @group Payment
 */
class PaymentWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_test', 'block', 'views');

  /**
   * Tests the payment UI.
   */
  protected function testPaymentUi() {
    $this->drupalPlaceBlock('local_tasks_block');
    $payment_method = Payment::methodManager()->createInstance('payment_test');
    // Create just enough payments for three pages
    $count_payments = 50 * 2 + 1;
    foreach (range(0, $count_payments) as $i) {
      $payment = Generate::createPayment(2, $payment_method);
      $payment->save();
      $payment = entity_load_unchanged('payment', $payment->id());
    }

    // View the administrative listing.
    $this->drupalLogin($this->drupalCreateUser(array('access administration pages')));
    $this->drupalGet('admin/content');
    $this->assertResponse('200');
    $this->assertNoLinkByHref('admin/content/payment');
    $this->drupalGet('admin/content/payment');
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('access administration pages', 'payment.payment.view.any')));
    $this->drupalGet('admin/content');
    $this->clickLink(t('Payments'));
    if ($this->assertResponse('200')) {
      $this->assertTitle(t('Payments | Drupal'));
      $this->assertText(t('Last updated'));
      $this->assertText(t('Payment method'));
      $this->assertText(t('Enter a comma separated list of user names.'));
      $this->assertText(t('EUR 24.20'));
      $this->assertText($payment_method->getPluginLabel());
      $count_pages = ceil($count_payments / 50);
      if ($count_pages) {
        foreach (range(1, $count_pages - 1) as $page) {
          $this->assertLinkByHref('&page=' . $page);
        }
        $this->assertNoLinkByHref('&page=' . ($page + 1));
      }
      $this->assertLinkByHref('payment/1');
      $this->clickLinkPartialName('Next');
      $this->assertUrl('admin/content/payment?changed_after=&changed_before=&=Apply&page=1');
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

    // Delete a payment.
    $path = 'payment/' . $payment->id() . '/delete';
    $this->drupalGet($path);
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment.delete.any', 'payment.payment.view.any')));
    $this->drupalGet($path);
    if ($this->assertResponse('200')) {
      $this->clickLink(t('Cancel'));
      $this->assertUrl('payment/' . $payment->id());
      $this->drupalGet($path);
      $this->drupalPostForm(NULL, [], t('Delete'));
      $this->assertResponse('200');
      $this->assertFalse((bool) \Drupal::entityManager()->getStorage('payment')->loadUnchanged($payment->id()));
    }
  }
}
