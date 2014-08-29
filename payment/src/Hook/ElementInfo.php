<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\ElementInfo.
 */

namespace Drupal\payment\Hook;

use Drupal\payment\Element\PaymentLineItemsInput;

/**
 * Implements hook_element_info().
 *
 * @see payment_element_info()
 */
class ElementInfo {

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    // Line item configuration. Use
    // \Drupal\payment\Element\PaymentLineItemsInput::getLineItemsData() to get
    // the 'return' value.
    $elements['payment_line_items_input'] = array(
      // The number of values this element allows, which must be at least as
      // many as the number of line items in the default value. For unlimited
      // values, use
      // \Drupal\payment\Element\PaymentLineItemsInput::CARDINALITY_UNLIMITED.
      '#cardinality' => PaymentLineItemsInput::CARDINALITY_UNLIMITED,
      // Values are arrays with two keys:
      // - plugin_id: the ID of the line item plugin instance.
      // - plugin_configuration: the configuration of the line item plugin
      //   instance.
      '#default_value' => array(),
      '#element_validate' => array(array('\Drupal\payment\Element\PaymentLineItemsInput', 'validate')),
      '#input' => TRUE,
      '#process' => array(array('\Drupal\Core\Render\Element\Container', 'processContainer'), array('\Drupal\payment\Element\PaymentLineItemsInput', 'process')),
      '#tree' => TRUE,
      '#theme_wrappers' => array('container'),
      '#value' => array(),
    );

    // Line item display.
    $elements['payment_line_items_display'] = array(
      // A \Drupal\payment\Entity\PaymentInterface object (required).
      '#payment' => NULL,
      '#pre_render' => array(array('\Drupal\payment\Element\PaymentLineItemsDisplay', 'preRender')),
    );

    // Payment statuses display.
    $elements['payment_statuses_display'] = array(
      // A \Drupal\payment\Entity\PaymentInterface object (required).
      '#payment' => NULL,
      '#pre_render' => array(array('\Drupal\payment\Element\PaymentStatusesDisplay', 'preRender')),
    );

    return $elements;
  }
}
