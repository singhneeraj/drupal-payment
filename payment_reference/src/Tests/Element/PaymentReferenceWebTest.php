<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Element\PaymentReferenceWebTest.
 */

namespace Drupal\payment_reference\Tests\Element;

use Drupal\payment\Generate;
use Drupal\payment_reference\PaymentReference;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the payment_reference element.
 */
class PaymentReferenceWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_reference', 'payment_reference_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => 'payment_reference element web test',
      'group' => 'Payment Reference Field',
    );
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $state = \Drupal::state();
    $path = 'payment_reference_test-element-payment_reference';

    // Test without queued payments.
    $this->drupalGet($path);
    $this->assertLinkByHref('payment_reference/pay/payment_reference_test_payment_reference_element');
    $this->drupalPostForm($path, array(), t('Submit'));
    $this->assertText('Foo field is required');
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertNull($value);

    // Test with a queued payment.
    $payment = Generate::createPayment(2);
    $payment->setStatus(\Drupal::service('plugin.manager.payment.status')->createInstance('payment_success'));
    $payment->save();
    PaymentReference::queue()->save('payment_reference_test_payment_reference_element', $payment->id());
    $this->drupalGet($path);
    $this->drupalPostForm($path, array(), t('Submit'));
    $value = $state->get('payment_reference_test_payment_reference_element');
    $this->assertEqual($value, $payment->id());
  }
}
