<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Manager.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\CacheDecorator;

/**
 * Manages discovery and instantiation of payment status plugins.
 *
 * @see \Drupal\payment\Plugin\payment\status\PaymentStatusInterface
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
    $this->discovery = new AnnotatedClassDiscovery('payment/status', $namespaces, $annotation_namespaces, 'Drupal\payment\Annotations\PaymentStatus');
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->discovery = new AlterDecorator($this->discovery, 'payment_status');
    $this->discovery = new CacheDecorator($this->discovery, 'payment_status');
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
      return parent::createInstance('payment_unknown', $configuration);
    }
  }

  /**
   * Returns payment method options.
   *
   * @return array
   *   Keys are plugin IDs. Values are plugin labels.
   */
  function options() {
    $options = array();
    foreach ($this->getDefinitions() as $entity_type => $definition) {
      $options[$entity_type] = $definition['label'];
    }

    return $options;
  }
}
