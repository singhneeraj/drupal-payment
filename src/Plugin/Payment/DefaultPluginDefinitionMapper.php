<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\DefaultPluginDefinitionMapper.
 */

namespace Drupal\payment\Plugin\Payment;

/**
 * Provides a default plugin definition mapper.
 */
class DefaultPluginDefinitionMapper implements PluginDefinitionMapperInterface {

  /**
   * {@inheritdoc}
   */
  public function getPluginId(array $plugin_definition) {
    return $this->getPluginDefinitionProperty($plugin_definition, 'id');
  }

  /**
   * {@inheritdoc}
   */
  public function getParentPluginId(array $plugin_definition) {
    return $this->hasPluginDefinitionProperty($plugin_definition, 'parent_id') ? $this->getPluginDefinitionProperty($plugin_definition, 'parent_id') : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginLabel(array $plugin_definition) {
    return $this->hasPluginDefinitionProperty($plugin_definition, 'label') ? $this->getPluginDefinitionProperty($plugin_definition, 'label') : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDescription(array $plugin_definition) {
    return $this->hasPluginDefinitionProperty($plugin_definition, 'description') ? $this->getPluginDefinitionProperty($plugin_definition, 'description') : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPluginDefinitionProperty(array $plugin_definition, $name) {
    return array_key_exists($name, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinitionProperty(array $plugin_definition, $name) {
    if (array_key_exists($name, $plugin_definition)) {
      return $plugin_definition[$name];
    }
    else {
      throw new \InvalidArgumentException(sprintf('Plugin definition property "%s" does not exist.', $name));
    }
  }

}
