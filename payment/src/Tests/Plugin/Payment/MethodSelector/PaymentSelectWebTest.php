<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\PaymentSelectWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodSelector;

use Drupal\payment\Generate;
use Drupal\payment\Payment;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests \Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect.
 */
class PaymentSelectWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('filter', 'payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect web test',
      'group' => 'Payment',
    );
  }

  /**
   * Creates a payment method.
   *
   * @return \Drupal\payment\Entity\PaymentMethodConfigurationInterface
   */
  protected function createPaymentMethod() {
    $payment_method = Generate::createPaymentMethodConfiguration(2, 'payment_basic');
    $payment_method->setPluginConfiguration(array(
      'brand_label' => $this->randomName(),
      'message_text' => $this->randomName(),
    ));
    $payment_method->save();

    return $payment_method;
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $this->doTestElement(FALSE);
    foreach (entity_load_multiple('payment_method_configuration') as $payment_method_configuration) {
      $payment_method_configuration->delete();
    }
    $this->doTestElement(TRUE);
  }

  /**
   * Tests the element.
   *
   * @param bool $tree
   *   Whether to test the element with #tree = TRUE or not.
   */
  protected function doTestElement($tree) {
    $state = \Drupal::state();
    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface $payment_method_manager */
    $payment_method_manager = Payment::methodManager();

    $path = 'payment_test-payment_method_selector-payment_select/' . (int) $tree;
    $name_prefix = $tree ? 'tree[payment_method][container]' : 'container';

    // Test the presence of default elements without available payment methods.
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][payment_method_id]');
    $this->assertNoFieldByName($name_prefix . '[select][change]', t('Choose payment method'));
    $this->assertText(t('There are no available payment methods.'));

    // Test the presence of default elements with one available payment method.
    $payment_method_1 = $this->createPaymentMethod();
    $payment_method_manager->clearCachedDefinitions();
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][payment_method_id]');
    $this->assertNoFieldByName($name_prefix . '[select][change]', t('Choose payment method'));
    $this->assertNoText(t('There are no available payment methods.'));

    // Test the presence of default elements with multiple available payment
    // methods.
    $payment_method_2 = $this->createPaymentMethod();
    $payment_method_manager->clearCachedDefinitions();
    $this->drupalGet($path);
    $this->assertFieldByName($name_prefix . '[select][payment_method_id]');
    $this->assertFieldByName($name_prefix . '[select][change]', t('Choose payment method'));
    $this->assertNoText(t('There are no available payment methods.'));

    // Choose a payment method.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][payment_method_id]' => 'payment_basic:' . $payment_method_1->id(),
    ), t('Choose payment method'));
    $this->assertFieldByName($name_prefix . '[select][payment_method_id]');
    $this->assertFieldByName($name_prefix . '[select][change]', t('Choose payment method'));
    $payment_method_1_configuration = $payment_method_1->getPluginConfiguration();
    $this->assertText($payment_method_1_configuration['message_text']);
    $payment_method_2_configuration = $payment_method_2->getPluginConfiguration();
    $this->assertNoText($payment_method_2_configuration['message_text']);

    // Change the payment method.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][payment_method_id]' => 'payment_basic:' . $payment_method_2->id(),
    ), t('Choose payment method'));
    $this->assertFieldByName($name_prefix . '[select][payment_method_id]');
    $this->assertFieldByName($name_prefix . '[select][change]', t('Choose payment method'));
    $this->assertText($payment_method_2_configuration['message_text']);
    $this->assertNoText($payment_method_1_configuration['message_text']);

    // Submit the form.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][payment_method_id]' => 'payment_basic:' . $payment_method_2->id(),
    ), t('Submit'));
    $payment_method = $state->get('payment_test_method_form_element');
    $this->assertEqual($payment_method->getPluginId(), 'payment_basic:' . $payment_method_2->id());
  }
}
