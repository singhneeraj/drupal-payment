<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\Menu.
 */

namespace Drupal\payment\Hook;

/**
 * Implements hook_menu().
 *
 * @see payment_menu()
 */
class Menu {

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    // Administration section.
    $items['admin/config/services/payment'] = array(
      'route_name' => 'payment.admin',
      'title' => 'Payment',
    );

    // Payments.
    $items['admin/payments'] = array(
      'route_name' => 'payment.payment.admin_list',
      'title' => 'Payments',
    );

    // Payment types.
    $items['admin/config/services/payment/type'] = array(
      'title' => 'Payment types',
      'route_name' => 'payment.payment_type.list',
    );

    // Payment methods.
    $items['admin/config/services/payment/method'] = array(
      'route_name' => 'payment.payment_method_plugin.list',
      'title' => 'Payment methods',
    );

    // Payment status overview.
    $items['admin/config/services/payment/status'] = array(
      'route_name' => 'payment.payment_status.list',
      'title' => 'Payment statuses',
    );

    return $items;
  }

}
