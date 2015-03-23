<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\PluginSelector\RadiosWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\PluginSelector;

use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\payment\Payment;
use Drupal\payment\Tests\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment\Plugin\Payment\PluginSelector\Radios web test.
 *
 * @group Payment
 */
class RadiosWebTest extends WebTestBase {

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
    $payment_method = Generate::createPaymentMethodConfiguration(2, 'payment_basic');
    $payment_method->setPluginConfiguration(array(
      'brand_label' => $this->randomMachineName(),
      'message_text' => $this->randomMachineName(),
    ));
    $payment_method->save();

    return $payment_method;
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
    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface $payment_method_manager */
    $payment_method_manager = Payment::methodManager();

    $payment_method_configuration_storage = \Drupal::entityManager()->getStorage('payment_method_configuration');
    $payment_method_configuration_storage->delete($payment_method_configuration_storage->loadMultiple());
    $payment_method_manager->clearCachedDefinitions();

    $path = 'payment_test-plugin_selector-advanced_plugin_selector_base/payment_radios/' . (int) $tree;
    $name_prefix = $tree ? 'tree[payment_method][container]' : 'container';

    // Test the presence of default elements without available plugins.
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][container][container][plugin_id]');
    $this->assertNoFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertText(t('There are no available options.'));

    // Test the presence of default elements with one available plugin.
    $payment_method_1 = $this->createPaymentMethod();
    $payment_method_manager->clearCachedDefinitions();
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertNoFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertNoText(t('There are no available options.'));

    // Test the presence of default elements with multiple available plugins.
    $payment_method_2 = $this->createPaymentMethod();
    $payment_method_manager->clearCachedDefinitions();
    $this->drupalGet($path);
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertNoText(t('There are no available options.'));

    // Choose a payment method.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'payment_basic:' . $payment_method_1->id(),
    ), t('Choose'));
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $payment_method_1_configuration = $payment_method_1->getPluginConfiguration();
    $this->assertText($payment_method_1_configuration['message_text']);
    $payment_method_2_configuration = $payment_method_2->getPluginConfiguration();
    $this->assertNoText($payment_method_2_configuration['message_text']);

    // Change the plugin.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'payment_basic:' . $payment_method_2->id(),
    ), t('Choose'));
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertText($payment_method_2_configuration['message_text']);
    $this->assertNoText($payment_method_1_configuration['message_text']);

    // Submit the form.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'payment_basic:' . $payment_method_2->id(),
    ), t('Submit'));
    $payment_method = $state->get('payment_test_method_form_element');
    $this->assertEqual($payment_method->getPluginId(), 'payment_basic:' . $payment_method_2->id());
  }
}
