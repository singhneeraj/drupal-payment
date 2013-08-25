<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentStatusUIWebTest.
 */

namespace Drupal\payment\Tests;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment status UI.
 */
class PaymentStatusUIWebTest extends WebTestBase {

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
      'name' => 'Payment status UI',
      'group' => 'Payment',
    );
  }

  /**
   * Tests listing().
   */
  protected function testListing() {
    $path = 'admin/config/services/payment/status';
    $this->drupalGet($path);
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_status.view')));
    $this->drupalGet($path);
    $this->assertResponse(200);
    $manager = $this->container->get('plugin.manager.payment.status');
    foreach ($manager->getDefinitions() as $definition) {
      $this->assertText($definition['label']);
      if ($definition['description']) {
        $this->assertText($definition['description']);
      }
    }
  }
}
