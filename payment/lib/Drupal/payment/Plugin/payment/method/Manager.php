<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Manager.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\String;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of payment method controller plugins.
 *
 * @see \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
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
    parent::__construct('payment/method', $namespaces, $annotation_namespaces, 'Drupal\payment\Annotations\PaymentMethod');
    $this->alterInfo($module_handler, 'payment_method');
    $this->setCacheBackend($cache_backend, $language_manager, 'payment_method');
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
      return parent::createInstance('payment_unavailable', $configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    // Merge in default operation values.
    foreach ($definition['operations'] as $operation => &$operation_definition) {
      if (empty($operation_definition['label'])) {
        throw new \InvalidArgumentException(String::format('Plugin !plugin_id does not define a label for operation !operation.', array(
          '!operation' => $operation,
          '!plugin_id' => $plugin_id,
        )));
      }
      $operation_definition += array(
        'interrupts_execution' => TRUE,
      );
    }
  }
}
