<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\PluginSelector\SelectListWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\PluginSelector;

use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\payment\Payment;
use Drupal\payment\Tests\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment\Plugin\Payment\PluginSelector\SelectList web test.
 *
 * @group Payment
 */
class SelectListWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('filter', 'payment_test');

  /**
   * Creates a payment method.
   *
   * @return \Drupal\payment\Entity\PaymentMethodConfigurationInterface
   */
  protected function createPaymentMethod() {
    $plugin = Generate::createPaymentMethodConfiguration(2, 'payment_basic');
    $plugin->setPluginConfiguration(array(
      'brand_label' => $this->randomMachineName(),
      'message_text' => $this->randomMachineName(),
    ));
    $plugin->save();

    return $plugin;
  }

  /**
   * Tests the element.
   */
  protected function testElement() {
    $this->doTestElement(FALSE);
    foreach (PaymentMethodConfiguration::loadMultiple() as $payment_method_configuration) {
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
    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface $plugin_manager */
    $plugin_manager = Payment::methodManager();

    $path = 'payment_test-plugin_selector-advanced_plugin_selector_base/payment_select_list/' . (int) $tree;
    $name_prefix = $tree ? 'tree[payment_method][container]' : 'container';

    // Test the presence of default elements without available options.
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][container][container][plugin_id]');
    $this->assertNoFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertText(t('There are no available options.'));

    // Test the presence of default elements with one available payment method.
    $plugin_1 = $this->createPaymentMethod();
    $plugin_manager->clearCachedDefinitions();
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertNoFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertNoText(t('There are no available options.'));

    // Test the presence of default elements with multiple available payment
    // methods.
    $plugin_2 = $this->createPaymentMethod();
    $plugin_manager->clearCachedDefinitions();
    $this->drupalGet($path);
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertNoText(t('There are no available options.'));

    // Choose a payment method.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'payment_basic:' . $plugin_1->id(),
    ), t('Choose'));
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $plugin_1_configuration = $plugin_1->getPluginConfiguration();
    $this->assertText($plugin_1_configuration['message_text']);
    $plugin_2_configuration = $plugin_2->getPluginConfiguration();
    $this->assertNoText($plugin_2_configuration['message_text']);

    // Change the payment method.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'payment_basic:' . $plugin_2->id(),
    ), t('Choose'));
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertText($plugin_2_configuration['message_text']);
    $this->assertNoText($plugin_1_configuration['message_text']);

    // Submit the form.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'payment_basic:' . $plugin_2->id(),
    ), t('Submit'));
    $plugin = $state->get('payment_test_method_form_element');
    $this->assertEqual($plugin->getPluginId(), 'payment_basic:' . $plugin_2->id());
  }
}
