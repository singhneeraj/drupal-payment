<?php

/**
 * Contains \Drupal\payment\Plugin\payment\line_item\Manager.
 */

namespace Drupal\payment\Plugin\payment\line_item;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of payment line item plugins.
 *
 * @see \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
 */
class Manager extends DefaultPluginManager {

  /**
   * Constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/payment/line_item', $namespaces, '\Drupal\payment\Annotations\PaymentLineItem');
    $this->alterInfo($module_handler, 'payment_line_item');
    $this->setCacheBackend($cache_backend, $language_manager, 'payment_line_item');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    // If a plugin is missing, use the default.
    try {
      return parent::createInstance($plugin_id, $configuration);
    }
    catch (PluginException $e) {
      return parent::createInstance('payment_basic', $configuration);
    }
  }

  /**
   * Returns payment line item options.
   *
   * @return array
   *   Keys are plugin IDs. Values are plugin labels.
   */
  public function options() {
    $options = array();
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }
    natcasesort($options);

    return $options;
  }
}
