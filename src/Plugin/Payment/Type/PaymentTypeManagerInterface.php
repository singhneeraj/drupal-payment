<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerInterface;

/**
 * Defines a payment type manager.
 */
interface PaymentTypeManagerInterface extends OperationsProviderPluginManagerInterface, PluginManagerInterface {

  /**
   * Creates a payment type.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface
   */
  public function createInstance($plugin_id, array $configuration = []);

}
