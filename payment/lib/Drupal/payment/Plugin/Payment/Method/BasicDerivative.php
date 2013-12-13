<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Method\BasicDerivative.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\Manager as PaymentMethodConfiguratonManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieves payment method plugin definitions based on configuration entities.
 *
 * @see \Drupal\payment\Plugin\Payment\Method\Basic
 */
class BasicDerivative extends DerivativeBase implements ContainerDerivativeInterface {

  /**
   * The payment method configuration manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\Manager
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment method storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $paymentMethodStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityStorageControllerInterface $payment_method_storage, PaymentMethodConfiguratonManager $payment_method_configuration_manager) {
    $this->paymentMethodStorage = $payment_method_storage;
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('entity.manager')->getStorageController('payment_method'), $container->get('plugin.manager.payment.method_configuration'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    /** @var \Drupal\payment\Entity\PaymentMethodInterface[] $payment_methods */
    $payment_methods = $this->paymentMethodStorage->loadMultiple();
    foreach ($payment_methods as $payment_method) {
      if ($payment_method->getPluginId() == 'payment_basic') {
        $configuration_plugin = $this->paymentMethodConfigurationManager->createInstance($payment_method->getPluginId(), $payment_method->getPluginConfiguration());
        $this->derivatives[$payment_method->id()] = array(
          'active' => $payment_method->status(),
          'label' => $configuration_plugin->getBrandLabel() ? $configuration_plugin->getBrandLabel() : $payment_method->label(),
          'message_text' => $configuration_plugin->getMessageText(),
          'message_text_format' => $configuration_plugin->getMessageTextFormat(),
          'status' => $configuration_plugin->getStatus(),
        ) + $base_plugin_definition;
      }
    }

    return $this->derivatives;
  }
}
