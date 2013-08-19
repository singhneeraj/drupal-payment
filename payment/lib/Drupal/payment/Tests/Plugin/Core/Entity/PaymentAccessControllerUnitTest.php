<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentAccessControllerUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\Entity;

use Drupal\payment\AccessibleInterfaceUnitTestBase;
use Drupal\payment\Generate;

/**
 * Tests \Drupal\payment\Plugin\Core\Entity\PaymentAccessController.
 */
class PaymentAccessControllerUnitTest extends AccessibleInterfaceUnitTestBase {

  public static $modules = array('payment', 'system', 'user');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Core\Entity\PaymentAccessController unit test',
      'group' => 'Payment',
    );
  }

  /**
   * Tests access control.
   */
  function testAccessControl() {
    $payment_1 = Generate::createPayment(1);
    $payment_2 = Generate::createPayment(2);
    $entity_manager = $this->container->get('plugin.manager.entity');
    $user_storage_controller = $entity_manager->getStorageController('user');
    $authenticated = $user_storage_controller->create(array(
      'uid' => 2,
    ));

    // Create a new payment.
    $this->assertDataAccess(entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    )), 'a payment', 'create', $authenticated, array(), array(
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
