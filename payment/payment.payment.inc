<?php

/**
 * @file
 * Payment hooks.
 */

/**
 * Implements hook_payment_line_item_info().
 */
function payment_payment_line_item_info() {
  return array(
    new PaymentLineItemInfo(array(
      'callback' => 'payment_line_item_get_all',
      'name' => 'payment_all',
      'title' => t('All items'),
    )),
  );
}