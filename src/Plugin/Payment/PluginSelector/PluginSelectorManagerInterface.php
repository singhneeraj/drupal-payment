<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment\PluginSelector;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines a plugin selector manager.
 */
interface PluginSelectorManagerInterface extends PluginManagerInterface {

  /**
   * Creates a plugin selector.
   *
   * @param string $plugin_id
   *   The id of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface
   */
  public function createInstance($plugin_id, array $configuration = []);

}
