<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Core\Entity\PaymentMethodAccessControllerWebTest.
 */

namespace Drupal\payment\Tests\Plugin\Core\Entity;

use Drupal\payment\AccessibleInterfaceWebTestBase;
use Drupal\payment\Generate;

/**
 * Tests \Drupal\payment\PaymentMethodAccessController.
 */
class PaymentMethodAccessControllerWebTest extends AccessibleInterfaceWebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\PaymentMethodAccessController web test',
      'group' => 'Payment',
    );
  }

  /**
   * Tests access control.
   */
  function testAccessControl() {
    $authenticated = $this->drupalCreateUser();

    // Create a new payment method.
    $manager = \Drupal::service('plugin.manager.payment.payment_method');
    $plugin = $manager->createInstance('payment_basic');
    $this->assertDataAccess(Generate::createPaymentMethod(0, $plugin), 'a payment method', 'create', $authenticated, array('payment.payment_method.create.payment_basic'));

    // Update a payment method that belongs to user 1.
    $this->assertDataAccess(Generate::createPaymentMethod(1), 'a payment method', 'update', $authenticated, array('payment.payment_method.update.any'));

    // Update a payment method that belongs to user 2.
    $this->assertDataAccess(Generate::createPaymentMethod($authenticated->uid), 'a payment method', 'update', $authenticated, array('payment.payment_method.update.own'));

    // Delete a payment method that belongs to user 1.
    $this->assertDataAccess(Generate::createPaymentMethod(1), 'a payment method', 'delete', $authenticated, array('payment.payment_method.delete.any'));

    // Delete a payment method that belongs to user 2.
    $this->assertDataAccess(Generate::createPaymentMethod($authenticated->uid), 'a payment method', 'delete', $authenticated, array('payment.payment_method.delete.own'));

    // Enable an enabled payment method that belongs to user 1.
    $payment_method = Generate::createPaymentMethod(1);
    $this->assertDataAccess($payment_method, 'an enabled payment method', 'enable', $authenticated, array('payment.payment_method.update.any'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Enable an enabled payment method that belongs to user 2.
    $payment_method = Generate::createPaymentMethod($authenticated->uid);
    $this->assertDataAccess($payment_method, 'an enabled payment method', 'enable', $authenticated, array('payment.payment_method.update.own'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Enable a disabled payment method that belongs to user 1.
    $payment_method = Generate::createPaymentMethod(1);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'enable', $authenticated, array('payment.payment_method.update.any'));

    // Enable a disabled payment method that belongs to user 2.
    $payment_method = Generate::createPaymentMethod($authenticated->uid);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'enable', $authenticated, array('payment.payment_method.update.own'));

    // Disable a disabled payment method that belongs to user 1.
    $payment_method = Generate::createPaymentMethod(1);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.any'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Disable a disabled payment method that belongs to user 2.
    $payment_method = Generate::createPaymentMethod($authenticated->uid);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.own'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Disable an enabled payment method that belongs to user 1.
    $payment_method = Generate::createPaymentMethod(1);
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.any'));

    // Enable am enabled payment method that belongs to user 2.
    $payment_method = Generate::createPaymentMethod($authenticated->uid);
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.own'));

    // Clone a payment method that belongs to user 1.
    // @todo Test this with a controller that actually has create permissions.
    // $this->assertDataAccess(Generate::createPaymentMethod(1), 'a payment method', 'clone', $authenticated, array('payment.payment_method.view.any', 'payment.payment_method.create.Drupal\\payment\\PaymentMethodControllerUnavailable'));

    // Clone a payment method that belongs to user 2.
    // @todo Test this with a controller that actually has create permissions.
    // $this->assertDataAccess(Generate::createPaymentMethod($authenticated->uid), 'a payment method', 'clone', $authenticated, array('payment.payment_method.view.own', 'payment.payment_method.create.Drupal\\payment\\PaymentMethodControllerUnavailable'));

    // View a payment method that belongs to user 1.
    $this->assertDataAccess(Generate::createPaymentMethod(1), 'a payment method', 'view', $authenticated, array('payment.payment_method.view.any'));

    // View a payment method that belongs to user 2.
    $this->assertDataAccess(Generate::createPaymentMethod($authenticated->uid), 'a payment method', 'view', $authenticated, array('payment.payment_method.view.own'));
  }
}
