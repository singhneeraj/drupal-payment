<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerInterface;

/**
 * Defines a payment method manager.
 */
interface PaymentMethodManagerInterface extends OperationsProviderPluginManagerInterface, PluginManagerInterface {

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
  public function createInstance($plugin_id, array $configuration = []);

}
