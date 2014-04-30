<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait.
 */

namespace Drupal\payment\Plugin\Payment;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages discovery and instantiation of payment status plugins.
 *
 * @see \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface
 */
trait OperationsProviderPluginManagerTrait {

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function getOperationsProvider($plugin_id) {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface|\Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait $this */
    $definition = $this->getDefinition($plugin_id);
    if (isset($definition['operations_provider'])) {
      $class = $definition['operations_provider'];
      if (class_implements($class, '\Drupal\Core\DependencyInjection\ContainerInjectionInterface')) {
        /** @var \Drupal\Core\DependencyInjection\ContainerInjectionInterface $class */
        return $class::create($this->container);
      }
      else {
        return new $class();
      }
    }
  }

}
