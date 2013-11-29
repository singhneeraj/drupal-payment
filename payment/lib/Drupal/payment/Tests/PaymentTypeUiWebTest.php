<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentTypeUiWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment type UI.
 */
class PaymentTypeUiWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('field_ui', 'payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'Payment type UI',
      'group' => 'Payment',
    );
  }

  /**
   * Tests administrative overview.
   */
  protected function testOverview() {
    $admin = $this->drupalCreateUser(array('access administration pages'));

    // Test the plugin listing.
    $this->drupalGet('admin/config/services/payment');
    $this->assertNoLink('Payment types');
    $this->drupalGet('admin/config/services/payment/type');
    $this->assertResponse('403');
    $this->drupalLogin($admin);
    $this->drupalGet('admin/config/services/payment');
    $this->assertLink('Payment types');
    $this->drupalGet('admin/config/services/payment/type');
    $this->assertResponse('200');
    $this->assertText(t('Test type'));

    // Test the dummy payment type route.
    $this->drupalGet('admin/config/services/payment/type/payment_test');
    $this->assertResponse('404');

    // Test field operations.
    $this->drupalLogout();
    $links = array(
      'administer payment display' => t('Manage display'),
      'administer payment fields' => t('Manage fields'),
      'administer payment form display' => t('Manage form display'),
    );
    $path = 'admin/config/services/payment/type';
    foreach ($links as $permission => $text) {
      $this->drupalLogin($admin);
      $this->drupalGet($path);
      $this->assertResponse('200');
      $this->assertNoLink($text);
      $this->drupalLogin($this->drupalCreateUser(array($permission, 'access administration pages')));
      $this->drupalGet($path);
      $this->clickLink($text);
      $this->assertResponse('200');
      $this->assertTitle($text . ' | Drupal');
    }

    // Test a type-specific operation.
    $this->drupalLogin($admin);
    $this->drupalGet($path);
    $this->assertResponse('200');
    $this->assertLink('FooBar');
  }
}
