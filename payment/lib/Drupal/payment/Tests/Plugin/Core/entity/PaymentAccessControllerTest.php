<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentAccessControllerTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\entity;

use Drupal\payment\Tests\AccessibleInterfaceWebTestBase;
use Drupal\payment\Tests\Utility;

/**
 * Tests \Drupal\payment\Plugin\Core\entity\PaymentAccessController.
 */
class PaymentAccessControllerTest extends AccessibleInterfaceWebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\Core\entity\PaymentAccessController',
      'group' => 'Payment',
    );
  }

  /**
   * Tests access control.
   */
  function testAccessControl() {
    $payment_1 = Utility::createPayment(1);
    $payment_2 = Utility::createPayment(2);
    $authenticated = $this->drupalCreateUser();

    // Create a new payment.
    $this->assertDataAccess(entity_create('payment', array()), 'a payment', 'create', $authenticated, array(), array(
      'anonymous' => TRUE,
      'authenticated_without_permissions' => TRUE,
    ));

    // Test deleting, updating and viewing a payment.
    $operations = array('delete', 'update', 'view');
    foreach ($operations as $operation) {
      // Test a payment that belongs to user 1.
      $data_label = 'a payment with UID ' . $payment_1->getOwnerId();
      $this->assertDataAccess($payment_1, $data_label, $operation, $authenticated, array("payment.payment.$operation.any"));
      $this->assertDataAccess($payment_1, $data_label, $operation, $authenticated, array("payment.payment.$operation.own"), array(
        'authenticated_with_permissions' => FALSE,
      ));
      $this->assertDataAccess($payment_1, $data_label, $operation, $authenticated);

      // Test a payment that belongs to user 2.
      $data_label = 'a payment with UID ' . $payment_2->getOwnerId();
      $this->assertDataAccess($payment_2, $data_label, $operation, $authenticated, array("payment.payment.$operation.any"));
      $this->assertDataAccess($payment_2, $data_label, $operation, $authenticated, array("payment.payment.$operation.own"));
      $this->assertDataAccess($payment_2, $data_label, $operation, $authenticated);
    }
  }
}
