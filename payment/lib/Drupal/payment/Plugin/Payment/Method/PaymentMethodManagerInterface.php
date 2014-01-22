<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines a payment method manager.
 */
interface PaymentMethodManagerInterface extends PluginManagerInterface {

  /**
   * Creates a payment method.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  public function createInstance($plugin_id, array $configuration = array());

  /**
   * Returns payment method options.
   *
   * @return array
   *   Keys are plugin IDs. Values are plugin labels.
   */
  public function options();

}
