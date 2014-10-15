<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\PaymentStatusManager.
 */

namespace Drupal\payment\Plugin\Payment\Status;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait;

/**
 * Manages discovery and instantiation of payment status plugins.
 *
 * @see \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface
 */
class PaymentStatusManager extends DefaultPluginManager implements PaymentStatusManagerInterface, FallbackPluginManagerInterface {

  use OperationsProviderPluginManagerTrait;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected $defaults = array(
    // The plugin ID. Set by the plugin system based on the top-level YAML key.
    'id' => NULL,
    // The plugin ID of the parent status (required).
    'parent_id' => NULL,
    // The human-readable plugin label (optional).
    'label' => NULL,
    // The human-readable plugin description (optional).
    'description' => NULL,
    // The name of the class that provides plugin operations. The class must
    // implement \Drupal\payment\Plugin\Payment\OperationsProviderInterface and
    // may implement
    // \Drupal\Core\DependencyInjection\ContainerInjectionInterface.
    'operations_provider' => NULL,
    // The default plugin class name. Any class must implement
    // \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface.
    'class' => 'Drupal\payment\Plugin\Payment\Status\DefaultPaymentStatus',
  );

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class_resolver.
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   *   The string translator.
   */
  public function __construct(CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ClassResolverInterface $class_resolver, TranslationInterface $string_translation) {
    $this->alterInfo('payment_status');
    $this->setCacheBackend($cache_backend, 'payment_status');
    $this->classResolver = $class_resolver;
    $this->discovery = new YamlDiscovery('payment.status', $module_handler->getModuleDirectories());
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    $this->factory = new ContainerFactory($this, 'Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $this->moduleHandler = $module_handler;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = array()) {
    return 'payment_unknown';
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    foreach (['description', 'label'] as $key) {
      if (isset($definition[$key])) {
        $definition[$key] = (new TranslationWrapper($definition[$key]))->setStringTranslation($this->stringTranslation);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function options(array $limit_plugin_ids = NULL) {
    return $this->optionsLevel($this->hierarchy($limit_plugin_ids), 0);
  }

  /**
   * {@inheritdoc}
   */
  public function hierarchy(array $limit_plugin_ids = NULL) {
    static $hierarchy = NULL;

    if (is_null($hierarchy)) {
      $parents = array();
      $children = array();
      $definitions = $this->getDefinitions();
      if (is_array($limit_plugin_ids)) {
        $definitions = array_intersect_key($definitions, array_flip($limit_plugin_ids));
      }
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
   * {@inheritdoc}
   */
  public function getAncestors($plugin_id) {
    $definition = $this->getDefinition($plugin_id);
    if (isset($definition['parent_id'])) {
      $parent_id = $definition['parent_id'];
      return array_unique(array_merge(array($parent_id), $this->getAncestors($parent_id)));
    }
    return array();
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getDescendants($plugin_id) {
    $child_plugin_ids = $this->getChildren($plugin_id);
    $descendant_plugin_ids = $child_plugin_ids;
    foreach ($child_plugin_ids as $child_plugin_id) {
      $descendant_plugin_ids = array_merge($descendant_plugin_ids, $this->getDescendants($child_plugin_id));
    }

    return array_unique($descendant_plugin_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function hasAncestor($plugin_id, $ancestor_plugin_id) {
    return in_array($ancestor_plugin_id, $this->getAncestors($plugin_id));
  }

  /**
   * {@inheritdoc}
   */
  public function isOrHasAncestor($plugin_id, $ancestor_plugin_id) {
    return $plugin_id == $ancestor_plugin_id || $this->hasAncestor($plugin_id, $ancestor_plugin_id);
  }

}
