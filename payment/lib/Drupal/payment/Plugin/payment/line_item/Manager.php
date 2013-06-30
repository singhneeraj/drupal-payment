<?php

/**
 * Contains \Drupal\payment\Plugin\payment\line_item\Manager.
 */

namespace Drupal\payment\Plugin\payment\line_item;

use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\CacheDecorator;

/**
 * Manages discovery and instantiation of payment line item plugins.
 *
 * @see \Drupal\payment\Plugin\payment\line_item\LineItemInterface
 */
class Manager extends PluginManagerBase {

  /**
   * Constructor.
   *
   * @param array $namespaces
   *   An array of paths keyed by their corresponding namespaces.
   */
  public function __construct(\Traversable $namespaces) {
    $annotation_namespaces = array(
      'Drupal\payment\Annotations' => drupal_get_path('module', 'payment') . '/lib',
    );
    $this->discovery = new AnnotatedClassDiscovery('payment/line_item', $namespaces, $annotation_namespaces, 'Drupal\payment\Annotations\LineItem');
    $this->discovery = new AlterDecorator($this->discovery, 'payment_line_item');
    $this->discovery = new CacheDecorator($this->discovery, 'payment_line_item');
    $this->factory = new DefaultFactory($this->discovery);
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
}
