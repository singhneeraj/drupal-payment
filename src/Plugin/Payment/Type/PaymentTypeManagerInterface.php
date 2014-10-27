<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines a payment type manager.
 */
interface PaymentTypeManagerInterface extends PluginManagerInterface {

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
  public function createInstance($plugin_id, array $configuration = array());

  /**
   * Gets the payment status' operations provider.
   *
   * @param string $plugin_id
   *
   * @return \Drupal\payment\Plugin\Payment\OperationsProviderInterface|null
   *   The operations provider or NULL if none is available..
   */
  public function getOperationsProvider($plugin_id);

}
