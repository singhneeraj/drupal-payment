<?php

namespace Drupal\payment\Tests\Controller;

use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Payment;
use Drupal\simpletest\WebTestBase;

/**
 * Payment method UI.
 *
 * @group Payment
 */
class PaymentMethodWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * Tests the different UI components.
   */
  protected function testUI() {
    $this->doTestList();
    $this->doTestEnableDisable();
    $this->doTestDuplicate();
    $this->doTestDelete();
    $this->doTestAddSelect();
    $this->doTestAdd();
  }

  /**
   * Tests the list.
   */
  protected function doTestList() {
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->assertResponse(200);
  }

  /**
   * Tests enabling/disabling.
   */
  protected function doTestEnableDisable() {
    $this->drupalLogout();
    // Confirm that there are no enable/disable links without the required
    // permissions.
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->assertNoLink(t('Enable'));
    $this->assertNoLink(t('Disable'));

    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method */
    $payment_method = entity_load('payment_method_configuration', 'collect_on_delivery');
    $this->assertFalse($payment_method->status());

    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any', 'payment.payment_method_configuration.update.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->clickLink(t('Enable'));
    $payment_method = entity_load_unchanged('payment_method_configuration', 'collect_on_delivery');
    $this->assertTrue($payment_method->status());

    $this->clickLink(t('Disable'));
    $payment_method = entity_load_unchanged('payment_method_configuration', 'collect_on_delivery');
    $this->assertFalse($payment_method->status());
  }

  /**
   * Tests duplication.
   */
  protected function doTestDuplicate() {
    $this->drupalLogout();
    $entity_id = 'collect_on_delivery';
    $plugin_id = 'payment_basic';
    $storage = \Drupal::entityManager()->getStorage('payment_method_configuration');

    // Test that only the original exists.
    $this->assertTrue((bool) $storage->load($entity_id));
    $this->assertFalse((bool) $storage->load($entity_id . '_duplicate'));

    // Test insufficient permissions.
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->assertNoLinkByHref(t('admin/config/services/payment/method/configuration/' . $entity_id . '/duplicate'));
    $this->drupalGet('admin/config/services/payment/method/configuration/' . $entity_id . '/duplicate');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration/' . $entity_id . '/duplicate');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.create.' . $plugin_id)));
    $this->drupalGet('admin/config/services/payment/method/configuration/' . $entity_id . '/duplicate');
    $this->assertResponse(403);

    // Test sufficient permissions.
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any', 'payment.payment_method_configuration.create.' . $plugin_id)));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->clickLink(t('Duplicate'));
    $this->assertResponse(200);
    $this->assertFieldByXPath('//form[@id="payment-method-configuration-payment-basic-form"]');
    $this->drupalPostForm(NULL, array(
      'id' => $entity_id . '_duplicate',
    ), t('Save'));
    $this->assertTrue((bool) $storage->load($entity_id));
    $this->assertTrue((bool) $storage->load($entity_id . '_duplicate'));
  }

  /**
   * Tests deletion.
   */
  protected function doTestDelete() {
    $this->drupalLogout();
    $id = 'collect_on_delivery';

    $this->drupalGet('admin/config/services/payment/method/configuration/' . $id . '/delete');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->assertNoLink(t('Delete'));

    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.view.any', 'payment.payment_method_configuration.delete.any')));
    $this->drupalGet('admin/config/services/payment/method/configuration');
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, [], t('Delete'));
    $this->assertFalse((bool) entity_load('payment_method_configuration', $id));
  }

  /**
   * Tests selecting.
   */
  protected function doTestAddSelect() {
    $this->drupalLogout();
    $plugin_id = 'payment_basic';
    $this->drupalGet('admin/config/services/payment/method/configuration-add');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method_configuration.create.' . $plugin_id)));
    $this->drupalGet('admin/config/services/payment/method/configuration-add');
    $this->assertResponse(200);
    $definition = Payment::methodConfigurationManager()->getDefinition($plugin_id);
    $this->assertText($definition['label']);
  }

  /**
   * Tests adding.
   */
  protected function doTestAdd() {
    $this->drupalLogout();
    $plugin_id = 'payment_basic';
    $this->drupalGet('admin/config/services/payment/method/configuration-add/' . $plugin_id);
    $this->assertResponse(403);
    $user = $this->drupalCreateUser(array('payment.payment_method_configuration.create.' . $plugin_id));
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/services/payment/method/configuration-add/' . $plugin_id);
    $this->assertResponse(200);
    $this->assertFieldByXPath('//form[@id="payment-method-configuration-payment-basic-form"]');

    // Test form validation.
    $this->drupalPostForm(NULL, array(
      'owner' => '',
    ), t('Save'));
    $this->assertFieldByXPath('//input[@id="edit-label" and contains(@class, "error")]');
    $this->assertFieldByXPath('//input[@id="edit-id" and contains(@class, "error")]');
    $this->assertFieldByXPath('//input[@id="edit-owner" and contains(@class, "error")]');

    // Test form submission and payment method creation.
    $label = $this->randomString();;
    $brand_label = $this->randomString();
    $execute_status_id = 'payment_failed';
    $capture_status_id = 'payment_success';
    $refund_status_id = 'payment_cancelled';
    $id = strtolower($this->randomMachineName());
    $this->drupalPostForm(NULL, array(
      'label' => $label,
      'id' => $id,
      'owner' => $user->label(),
      'plugin_form[plugin_form][brand_label]' => $brand_label,
      'plugin_form[plugin_form][execute][execute_status][container][select][container][plugin_id]' => $execute_status_id,
      'plugin_form[plugin_form][capture][capture]' => TRUE,
      'plugin_form[plugin_form][capture][plugin_form][capture_status][container][select][container][plugin_id]' => $capture_status_id,
      'plugin_form[plugin_form][refund][refund]' => TRUE,
      'plugin_form[plugin_form][refund][plugin_form][refund_status][container][select][container][plugin_id]' => $refund_status_id,
    ), t('Save'));
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method */
    $payment_method = entity_load('payment_method_configuration', $id);
    if ($this->assertTrue($payment_method instanceof PaymentMethodConfigurationInterface)) {
      $this->assertEqual($payment_method->label(), $label);
      $this->assertEqual($payment_method->id(), $id);
      $this->assertEqual($payment_method->getOwnerId(), $user->id());
      $plugin_configuration = $payment_method->getPluginConfiguration();
      $this->assertEqual($plugin_configuration['brand_label'], $brand_label);
      $this->assertEqual($plugin_configuration['execute_status_id'], $execute_status_id);
      $this->assertEqual($plugin_configuration['capture'], TRUE);
      $this->assertEqual($plugin_configuration['capture_status_id'], $capture_status_id);
    }
  }
}
