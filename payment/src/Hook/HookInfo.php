<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\HookInfo.
 */

namespace Drupal\payment\Hook;

/**
 * Implements hook_hook_info().
 *
 * @see payment_hook_info()
 */
class HookInfo {

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    $hooks['payment_execute_access'] = array(
      'group' => 'payment',
    );
    $hooks['payment_line_item_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_method_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_method_configuration_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_method_selector_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_pre_capture'] = array(
      'group' => 'payment',
    );
    $hooks['payment_pre_execute'] = array(
      'group' => 'payment',
    );
    $hooks['payment_pre_refund'] = array(
      'group' => 'payment',
    );
    $hooks['payment_queue_payment_ids_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_status_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_status_set'] = array(
      'group' => 'payment',
    );
    $hooks['payment_type_alter'] = array(
      'group' => 'payment',
    );
    $hooks['payment_type_pre_resume_context'] = array(
      'group' => 'payment',
    );

    return $hooks;
  }

}
