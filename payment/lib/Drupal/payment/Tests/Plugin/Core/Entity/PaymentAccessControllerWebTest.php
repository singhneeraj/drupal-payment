<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentAccessControllerWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\Entity;

use Drupal\payment\AccessibleInterfaceWebTestBase;
use Drupal\payment\Generate;

/**
 * Tests \Drupal\payment\Plugin\Core\Entity\PaymentAccessController.
 */
class PaymentAccessControllerWebTest extends AccessibleInterfaceWebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\Core\Entity\PaymentAccessController web test',
      'group' => 'Payment',
    );
  }

  /**
   * Tests access control.
   */
  function testAccessControl() {
    $payment_1 = Generate::createPayment(1);
    $payment_2 = Generate::createPayment(2);
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
