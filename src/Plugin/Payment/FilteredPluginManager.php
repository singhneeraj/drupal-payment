<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\FilteredPluginManager.
 */

namespace Drupal\payment\Plugin\Payment;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Provides a filtered plugin manager.
 */
class FilteredPluginManager implements CachedDiscoveryInterface, FilteredPluginManagerInterface, PluginManagerInterface {

  use DependencySerializationTrait;
  use DiscoveryTrait;

  /**
   * The filtered plugin definitions.
   *
   * @var array[]|null
   *   An array with plugin definitions or NULL if the definitions have not been
   *   loaded yet.
   *
   * @see self::getDefinitions()
   */
  protected $pluginDefinitions;

  /**
   * The plugin definition mapper.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginDefinitionMapperInterface
   */
  protected $pluginDefinitionMapper;

  /**
   * The plugin ID filter.
   *
   * @var string[]|null
   *   An array of plugin IDs or NULL if the filter is not set.
   */
  protected $pluginIdFilter;

  /**
   * The original plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Whether or not to use plugin caching.
   *
   * @var bool
   */
  protected $useCaches = TRUE;

  /**
   * Creates a new instance.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Another plugin manager that the filters are applied to.
   * @param \Drupal\payment\Plugin\Payment\PluginDefinitionMapperInterface $plugin_definition_mapper
   */
  public function __construct(PluginManagerInterface $plugin_manager, PluginDefinitionMapperInterface $plugin_definition_mapper) {
    $this->pluginManager = $plugin_manager;
    $this->pluginDefinitionMapper = $plugin_definition_mapper;
  }

  /**
   * Filters a definition.
   *
   * @param array $plugin_definition
   *
   * @return bool
   *   Whether the definition should be kept.
   */
  protected function filterDefinition(array $plugin_definition) {
    return is_array($this->pluginIdFilter) ? in_array($this->pluginDefinitionMapper->getPluginId($plugin_definition), $this->pluginIdFilter) : TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    if (is_null($this->pluginDefinitions) || !$this->useCaches) {
      $this->pluginDefinitions = array_filter($this->pluginManager->getDefinitions(), [$this, 'filterDefinition']);
    }
    return $this->pluginDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    if ($this->hasDefinition($plugin_id)) {
      return $this->pluginManager->createInstance($plugin_id, $configuration);
    }
    else {
      throw new PluginNotFoundException($plugin_id);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    throw new \BadMethodCallException('This method is not supported. See https://www.drupal.org/node/1894130.');
  }

  /**
   * {@inheritdoc}
   */
  public function useCaches($use_caches = FALSE) {
    $this->useCaches = $use_caches;
    $plugin_manager = $this->pluginManager;
    if ($plugin_manager instanceof CachedDiscoveryInterface) {
      $plugin_manager->useCaches($use_caches);
    }
    $this->clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $this->pluginDefinitions = NULL;
    $plugin_manager = $this->pluginManager;
    if ($plugin_manager instanceof CachedDiscoveryInterface) {
      $plugin_manager->clearCachedDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginIdFilter(array $plugin_ids) {
    $this->pluginIdFilter = $plugin_ids;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function resetPluginIdFilter() {
    $this->pluginIdFilter = NULL;
    $this->clearCachedDefinitions();

    return $this;
  }

}
