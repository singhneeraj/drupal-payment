<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\MenuLinkDefaults.
 */

namespace Drupal\payment\Hook;

/**
 * Implements hook_menu().
 *
 * @see payment_menu()
 */
class MenuLinkDefaults {

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    $items['payment.admin'] = array(
      'route_name' => 'payment.admin',
      'link_title' => 'Payment',
      'parent' => 'system.admin.config.services',
    );

    $items['payment.payment.admin_list'] = array(
      'route_name' => 'payment.payment.admin_list',
      'link_title' => 'Payments',
      'parent' => 'system.admin',
    );

    $items['payment.payment_type.list'] = array(
      'link_title' => 'Payment types',
      'route_name' => 'payment.payment_type.list',
      'parent' => 'payment.admin',
    );

    $items['payment.payment_method_plugin.list'] = array(
      'route_name' => 'payment.payment_method_plugin.list',
      'link_title' => 'Payment methods',
      'parent' => 'payment.admin',
    );

    $items['payment.payment_status.list'] = array(
      'route_name' => 'payment.payment_status.list',
      'link_title' => 'Payment statuses',
      'parent' => 'payment.admin',
    );

    return $items;
  }

}
