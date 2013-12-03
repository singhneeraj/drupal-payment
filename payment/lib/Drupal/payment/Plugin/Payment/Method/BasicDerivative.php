<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Method\BasicDerivative.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\payment\Payment;

/**
 * Retrieves payment method plugin definitions based on configuration entities.
 *
 * @see \Drupal\payment\Plugin\Payment\Method\Basic
 */
class BasicDerivative extends DerivativeBase {

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
   * Returns the payment method configuration manager.
   */
  protected function getPaymentMethodConfigurationManager() {
    if (!$this->paymentMethodConfigurationManager) {
      $this->paymentMethodConfigurationManager = Payment::methodConfigurationManager();
    }

    return $this->paymentMethodConfigurationManager;
  }

  /**
   * Returns the payment method storage controller.
   */
  protected function getPaymentMethodStorage() {
    if (!$this->paymentMethodStorage) {
      $this->paymentMethodStorage = \Drupal::entityManager()->getStorageController('payment_method');
    }

    return $this->paymentMethodStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    $payment_methods = $this->getPaymentMethodStorage()->loadMultiple();
    foreach ($payment_methods as $payment_method) {
      if ($payment_method->getPluginId() == 'payment_basic') {
        $configuration_plugin = $this->getPaymentMethodConfigurationManager()->createInstance($payment_method->getPluginId(), $payment_method->getPluginConfiguration());
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
