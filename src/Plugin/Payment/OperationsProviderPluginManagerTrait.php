<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait.
 */

namespace Drupal\payment\Plugin\Payment;

/**
 * Manages discovery and instantiation of payment status plugins.
 *
 * @see \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface
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
    if (isset($definition['operations_provider'])) {
      return $this->classResolver->getInstanceFromDefinition($definition['operations_provider']);
    }
  }

}
