<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines a payment method selector manager.
 */
interface PaymentMethodSelectorManagerInterface extends PluginManagerInterface {

  /**
   * Creates a payment method selector.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface
   */
  public function createInstance($plugin_id, array $configuration = []);

  /**
   * Returns payment method selector options.
   *
   * @return array
   *   Keys are plugin IDs. Values are plugin labels.
   */
  public function options();

}
