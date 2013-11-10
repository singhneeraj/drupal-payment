<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentMethodUIWebTest.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Payment;
use Drupal\simpletest\WebTestBase ;

/**
 * Tests the payment method UI.
 */
class PaymentMethodUIWebTest extends WebTestBase {

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
      'name' => 'Payment method UI',
      'group' => 'Payment',
    );
  }

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
    $this->drupalGet('admin/config/services/payment/method');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any')));
    $this->drupalGet('admin/config/services/payment/method');
    $this->assertResponse(200);
  }

  /**
   * Tests enabling/disabling.
   */
  protected function doTestEnableDisable() {
    $this->drupalLogout();
    // Confirm that there are no enable/disable links without the required
    // permissions.
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any')));
    $this->drupalGet('admin/config/services/payment/method');
    $this->assertNoLink(t('Enable'));
    $this->assertNoLink(t('Disable'));

    $payment_method = entity_load('payment_method', 'collect_on_delivery');
    $this->assertFalse($payment_method->status());

    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any', 'payment.payment_method.update.any')));
    $this->drupalGet('admin/config/services/payment/method');
    $this->clickLink(t('Enable'));
    $payment_method = entity_load_unchanged('payment_method', 'collect_on_delivery');
    $this->assertTrue($payment_method->status());

    $this->clickLink(t('Disable'));
    $payment_method = entity_load_unchanged('payment_method', 'collect_on_delivery');
    $this->assertFalse($payment_method->status());
  }

  /**
   * Tests duplication.
   */
  protected function doTestDuplicate() {
    $this->drupalLogout();
    $entity_id = 'collect_on_delivery';
    $plugin_id = 'payment_basic';
    $storage = \Drupal::entityManager()->getStorageController('payment_method');

    // Test that only the original exists.
    $this->assertTrue((bool) $storage->load($entity_id));
    $this->assertFalse((bool) $storage->load($entity_id . '_duplicate'));

    // Test insufficient permissions.
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any')));
    $this->drupalGet('admin/config/services/payment/method');
    $this->assertNoLinkByHref(t('admin/config/services/payment/method/' . $entity_id . '/duplicate'));
    $this->drupalGet('admin/config/services/payment/method/' . $entity_id . '/duplicate');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any')));
    $this->drupalGet('admin/config/services/payment/method/' . $entity_id . '/duplicate');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.create.' . $plugin_id)));
    $this->drupalGet('admin/config/services/payment/method/' . $entity_id . '/duplicate');
    $this->assertResponse(403);

    // Test sufficient permissions.
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any', 'payment.payment_method.create.' . $plugin_id)));
    $this->drupalGet('admin/config/services/payment/method');
    $this->clickLink(t('Duplicate'));
    $this->assertResponse(200);
    $this->assertFieldByXPath('//form[@id="payment-basic-payment-method-form"]');
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

    $this->drupalGet('admin/config/services/payment/method/' . $id . '/delete');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any')));
    $this->drupalGet('admin/config/services/payment/method');
    $this->assertNoLink(t('Delete'));

    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.view.any', 'payment.payment_method.delete.any')));
    $this->drupalGet('admin/config/services/payment/method');
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, array(), t('Confirm'));
    $this->assertFalse((bool) entity_load('payment_method', $id));
  }

  /**
   * Tests selecting.
   */
  protected function doTestAddSelect() {
    $this->drupalLogout();
    $plugin_id = 'payment_basic';
    $this->drupalGet('admin/config/services/payment/method-add');
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_method.create.' . $plugin_id)));
    $this->drupalGet('admin/config/services/payment/method-add');
    $this->assertResponse(200);
    $definition = Payment::methodManager()->getDefinition($plugin_id);
    $this->assertText($definition['label']);
  }

  /**
   * Tests adding.
   */
  protected function doTestAdd() {
    $this->drupalLogout();
    $plugin_id = 'payment_basic';
    $this->drupalGet('admin/config/services/payment/method-add/' . $plugin_id);
    $this->assertResponse(403);
    $user = $this->drupalCreateUser(array('payment.payment_method.create.' . $plugin_id));
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/services/payment/method-add/' . $plugin_id);
    $this->assertResponse(200);
    $this->assertFieldByXPath('//form[@id="payment-basic-payment-method-form"]');

    // Test form validation.
    $this->drupalPostForm(NULL, array(
      'owner' => '',
    ), t('Save'));
    $this->assertFieldByXPath('//input[@id="edit-label" and contains(@class, "error")]');
    $this->assertFieldByXPath('//input[@id="edit-id" and contains(@class, "error")]');
    $this->assertFieldByXPath('//input[@id="edit-owner" and contains(@class, "error")]');

    // Test form submission and payment method creation.
    $label = $this->randomString();;
    $brand_option = $this->randomString();
    $id = strtolower($this->randomName());
    $this->drupalPostForm(NULL, array(
      'label' => $label,
      'id' => $id,
      'owner' => $user->label(),
      'plugin_form[brand]' => $brand_option,
    ), t('Save'));
    $payment_method = entity_load('payment_method', $id);
    if ($this->assertTrue($payment_method instanceof PaymentMethodInterface)) {
      $this->assertEqual($payment_method->label(), $label);
      $this->assertEqual($payment_method->id(), $id);
      $this->assertEqual($payment_method->getOwnerId(), $user->id());
      $brands = $payment_method->brands();
      $this->assertEqual($brands['default']['label'], $brand_option);
    }
  }
}
