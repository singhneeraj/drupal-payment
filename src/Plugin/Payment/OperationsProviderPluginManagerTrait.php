<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait.
 */

namespace Drupal\payment\Plugin\Payment;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * Implements \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerInterface.
 */
trait OperationsProviderPluginManagerTrait {

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * {@inheritdoc}
   */
  public function getOperationsProvider($plugin_id) {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface|\Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait $this */
    $definition = $this->getDefinition($plugin_id);
    if ($definition) {
      if (isset($definition['operations_provider'])) {
        return $this->classResolver->getInstanceFromDefinition($definition['operations_provider']);
      }
      return NULL;
    }
    else {
      throw new PluginNotFoundException($plugin_id);
    }
  }

}
