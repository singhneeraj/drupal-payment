<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Manager.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of payment status plugins.
 *
 * @see \Drupal\payment\Plugin\payment\status\PaymentStatusInterface
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
    $annotation_namespaces = array(
      'Drupal\payment\Annotations' => drupal_get_path('module', 'payment') . '/lib',
    );
    parent::__construct('Plugin/payment/status', $namespaces, $annotation_namespaces, 'Drupal\payment\Annotations\PaymentStatus');
    $this->alterInfo($module_handler, 'payment_status');
    $this->setCacheBackend($cache_backend, $language_manager, 'payment_status');
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
      return parent::createInstance('payment_unknown', $configuration);
    }
  }

  /**
   * Returns payment method options.
   *
   * @return array
   *   Keys are plugin IDs. Values are plugin labels.
   */
  public function options() {
    return $this->optionsLevel($this->hierarchy(), 0);
  }

  /**
   * Returns a hierarchical representation of payment statuses.
   *
   * @return array
   *   A possibly infinitely nested associative array. Keys are plugin IDs and
   *   values are arrays of similar structure as this method's return value.
   */
  public function hierarchy() {
    static $hierarchy = NULL;

    if (is_null($hierarchy)) {
      $parents = $children = array();
      $definitions = $this->getDefinitions();
      uasort($definitions, array($this, 'sort'));
      foreach ($definitions as $plugin_id => $definition) {
        if (!empty($definition['parent_id'])) {
          $children[$definition['parent_id']][] = $plugin_id;
        }
        else {
          $parents[] = $plugin_id;
        }
      }
      $hierarchy = $this->hierarchyLevel($parents, $children);
    }

    return $hierarchy;
  }

  /**
   * Helper function for self::hierarchy().
   *
   * @param array $parent_plugin_ids
   *   An array with IDs of plugins that are part of the same hierarchy level.
   * @param array $child_plugin_ids
   *   Keys are plugin IDs. Values are arrays with those plugin's child
   *   plugin IDs.
   *
   * @return array
   *   The return value is identical to that of self::hierarchy().
   */
  protected function hierarchyLevel(array $parent_plugin_ids, array $child_plugin_ids) {
    $hierarchy = array();
    foreach ($parent_plugin_ids as $plugin_id) {
      $hierarchy[$plugin_id] = isset($child_plugin_ids[$plugin_id]) ? $this->hierarchyLevel($child_plugin_ids[$plugin_id], $child_plugin_ids) : array();
    }

    return $hierarchy;
  }

  /**
   * Helper function for self::options().
   *
   * @param array $hierarchy
   *   A plugin ID hierarchy as returned by self::hierarchy().
   * @param integer $depth
   *   The depth of $hierarchy's top-level items as seen from the original
   *   hierarchy's root (this function is recursive), starting with 0.
   *
   * @return array
   *   The return value is identical to that of self::options().
   */
  protected function optionsLevel(array $hierarchy, $depth) {
    $definitions = $this->getDefinitions();
    $options = array();
    $prefix = $depth ? str_repeat('-', $depth) . ' ' : '';
    foreach ($hierarchy as $plugin_id => $child_plugin_ids) {
      $options[$plugin_id] = $prefix . $definitions[$plugin_id]['label'];
      $options += $this->optionsLevel($child_plugin_ids, $depth + 1);
    }

    return $options;
  }

  /**
   * Implements uasort() callback to sort plugin definitions by label.
   */
  protected function sort(array $definition_a, array $definition_b) {
    return strcmp($definition_a['label'], $definition_b['label']);
  }

  /**
   * Gets a payment status's ancestors.
   *
   * @param string $plugin_id
   *
   * @return array
   *   The plugin IDs of this status's ancestors.
   */
  function getAncestors($plugin_id) {
    $definition = $this->getDefinition($plugin_id);
    if (isset($definition['parent_id'])) {
      $parent_id = $definition['parent_id'];
      return array_unique(array_merge(array($parent_id), $this->getAncestors($parent_id)));
    }
    return array();
  }

  /**
   * Gets a payment status's children.
   *
   * @param string $plugin_id
   *
   * @return array
   *   The plugin IDs of this status's children.
   */
  public function getChildren($plugin_id) {
    $child_plugin_ids = array();
    foreach ($this->getDefinitions() as $definition) {
      if (isset($definition['parent_id']) && $definition['parent_id'] == $plugin_id) {
        $child_plugin_ids[] = $definition['id'];
      }
    }

    return $child_plugin_ids;
  }

  /**
   * Get a payment status's descendants.
   *
   * @param string $plugin_id
   *
   * @return array
   *   The machine names of this status's descendants.
   */
  function getDescendants($plugin_id) {
    $child_plugin_ids = $this->getChildren($plugin_id);
    $descendant_plugin_ids = $child_plugin_ids;
    foreach ($child_plugin_ids as $child_plugin_id) {
      $descendant_plugin_ids = array_merge($descendant_plugin_ids, $this->getDescendants($child_plugin_id));
    }

    return array_unique($descendant_plugin_ids);
  }

  /**
   * Checks if a status has a given other status as one of its ancestors.
   *
   * @param string $plugin_id
   * @param string $ancestor_plugin_id
   *   The payment status plugin ID to check against.
   *
   * @return boolean
   */
  function hasAncestor($plugin_id, $ancestor_plugin_id) {
    return in_array($ancestor_plugin_id, $this->getAncestors($plugin_id));
  }

  /**
   * Checks if the status is equal to a given other status or has it one of
   * its ancestors.
   *
   * @param string $plugin_id
   * @param string $ancestor_plugin_id
   *   The payment status plugin ID to check against.
   *
   * @return boolean
   */
  function isOrHasAncestor($plugin_id, $ancestor_plugin_id) {
    return $plugin_id == $ancestor_plugin_id|| $this->hasAncestor($plugin_id, $ancestor_plugin_id);
  }
}
