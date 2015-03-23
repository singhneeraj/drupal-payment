<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\PluginDefinitionMapperInterface.
 */

namespace Drupal\payment\Plugin\Payment;

/**
 * Defines a plugin definition mapper.
 *
 * Plugin definition mappers provide APIs around array plugin definitions which
 * are untyped and therefore not portable across APIs.
 */
interface PluginDefinitionMapperInterface {

  /**
   * Gets the ID for a plugin.
   *
   * @param array $plugin_definition
   *
   * @return string
   */
  public function getPluginId(array $plugin_definition);

  /**
   * Gets the plugin's parent's ID.
   *
   * @param array $plugin_definition
   *
   * @return string|null
   *   A plugin ID or NULL if no parent was specified.
   */
  public function getParentPluginId(array $plugin_definition);

  /**
   * Gets the label for a plugin.
   *
   * @param array $plugin_definition
   *
   * @return string|null
   */
  public function getPluginLabel(array $plugin_definition);

  /**
   * Gets the label for a plugin.
   *
   * @param array $plugin_definition
   *
   * @return string|null
   */
  public function getPluginDescription(array $plugin_definition);

  /**
   * Checks if the plugin has an arbitrary property.
   *
   * @param array $plugin_definition
   * @param string $name
   *   The property name.
   *
   * @return bool
   */
  public function hasPluginDefinitionProperty(array $plugin_definition, $name);

  /**
   * Gets the value for an arbitrary plugin property.
   *
   * @param array $plugin_definition
   * @param string $name
   *   The property name.
   *
   * @return mixed
   *   The property value.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the requested property does not exist.
   *
   * @see self::hasPluginDefinitionProperty
   */
  public function getPluginDefinitionProperty(array $plugin_definition, $name);

}
