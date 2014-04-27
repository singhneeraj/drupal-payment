<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\LineItem;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines a payment line item manager.
 */
interface PaymentLineItemManagerInterface extends PluginManagerInterface {

  /**
   * Creates a payment line item.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface
   */
  public function createInstance($plugin_id, array $configuration = array());

  /**
   * Returns payment line item options.
   *
   * @return array
   *   Keys are plugin IDs. Values are plugin labels.
   */
  public function options();

  /**
   * Returns the class name for a plugin ID.
   *
   * @param string $plugin_id
   *
   * @return string
   */
  public function getPluginClass($plugin_id);

}
