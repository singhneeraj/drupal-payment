<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment;

/**
 * Defines a plugin manager that can get operations providers for plugins.
 */
interface OperationsProviderPluginManagerInterface {

  /**
   * Gets the plugin's operations provider.
   *
   * @param string $plugin_id
   *
   * @return \Drupal\payment\Plugin\Payment\OperationsProviderInterface|null
   *   The operations provider or NULL if none is available.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getOperationsProvider($plugin_id);

}
