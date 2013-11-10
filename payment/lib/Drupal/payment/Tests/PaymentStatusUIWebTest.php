<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentStatusUIWebTest.
 */

namespace Drupal\payment\Tests;
use Drupal\payment\Payment;
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
   * The payment status storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  public $paymentStatusStorage;

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->paymentStatusStorage = \Drupal::entityManager()->getStorageController('payment_status');
  }

  /**
   * Tests the different UI components.
   */
  protected function testUI() {
    $this->doTestList();
    $this->doTestAdd();
    $this->doTestDelete();
  }

  /**
   * Tests listing.
   */
  protected function doTestList() {
    $path = 'admin/config/services/payment/status';
    $this->drupalGet($path);
    $this->assertResponse(403);
    $this->drupalLogin($this->drupalCreateUser(array('payment.payment_status.administer')));
    $this->drupalGet($path);
    $this->assertResponse(200);
    $manager = Payment::statusManager();
    foreach ($manager->getDefinitions() as $definition) {
      $this->assertText($definition['label']);
      if ($definition['description']) {
        $this->assertText($definition['description']);
      }
    }
  }

  /**
   * Tests adding a payment status.
   */
  protected function doTestAdd() {
    $this->drupalGet('admin/config/services/payment/status');
    $this->assertResponse('200');
    $path = 'admin/config/services/payment/status/add';
    if ($this->assertLinkByHref($path)) {
      $this->clickLink(t('Add payment status'));
      $id = strtolower($this->randomName());
      $label = $this->randomString();

      // Test a valid submission.
      $parent_id = 'payment_success';
      $description = $this->randomString();
      $this->drupalPostForm($path, array(
        'label' => $label,
        'id' => $id,
        'parent_id' => $parent_id,
        'description' => $description,
      ), t('Save'));
      $status = $this->paymentStatusStorage->loadUnchanged($id);
      if ($this->assertTrue((bool) $status)) {
        $this->assertEqual($status->id(), $id);
        $this->assertEqual($status->label(), $label);
        $this->assertEqual($status->getParentId(), $parent_id);
        $this->assertEqual($status->getDescription(), $description);
      }

      // Test an invalid submission.
      $this->drupalPostForm($path, array(
        'label' => $label,
        'id' => $id,
      ), t('Save'));
      $this->assertFieldByXPath('//input[@id="edit-id" and contains(@class, "error")]');
    }
  }

  /**
   * Tests deleting a payment status.
   */
  protected function doTestDelete() {
    $id = strtolower($this->randomName());
    $status = $this->paymentStatusStorage->create(array())
      ->setId($id);
    $status->save();
    if ($this->assertTrue((bool) $this->paymentStatusStorage->loadUnchanged($id))) {
      $status->delete();
      $this->assertFalse((bool) $this->paymentStatusStorage->loadUnchanged($id));
    }
  }
}
