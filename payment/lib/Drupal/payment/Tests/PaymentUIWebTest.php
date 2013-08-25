<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentUIWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment UI.
 */
class PaymentUIWebTest extends WebTestBase {

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
      'name' => 'Payment UI',
      'group' => 'Payment',
    );
  }

  /**
   * Tests administrative overview.
   */
  protected function testOverview() {
    $this->drupalGet('admin/config/services');
    $this->assertNoLink('Payment');
    $this->drupalGet('admin/config/services/payment');
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('access administration pages')));
    $this->drupalGet('admin/config/services');
    $this->assertLink('Payment');
    $this->drupalGet('admin/config/services/payment');
    $this->assertResponse('200');
  }
}
