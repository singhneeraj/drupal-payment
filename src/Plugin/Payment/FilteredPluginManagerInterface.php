<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\FilteredPluginManagerInterface.
 */

namespace Drupal\payment\Plugin\Payment;

/**
 * Defines a filtered plugin manager.
 */
interface FilteredPluginManagerInterface {

  /**
   * Sets which plugins should be managed.
   *
   * If this filter is set, any action for any plugin ID that is not part of the
   * filter must result in a
   * \Drupal\Component\Plugin\Exception\PluginNotFoundException being thrown.
   *
   * @param string[] $plugin_ids
   *   An array of plugin IDs or TRUE to allow all.
   *
   * @return $this
   */
  public function setPluginIdFilter(array $plugin_ids);

  /**
   * Resets which plugins should be managed.
   *
   * @return $this
   */
  public function resetPluginIdFilter();

}
