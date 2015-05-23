<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\PaymentAwarePluginFilteredPluginManager.
 */

namespace Drupal\payment\Plugin\Payment;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\PaymentAwareInterface;
use Drupal\plugin_selector\Plugin\FilteredPluginManager;
use Drupal\plugin_selector\Plugin\PluginDefinitionMapperInterface;

/**
 * Provides a filtered plugin manager for payment-aware plugins.
 */
class PaymentAwarePluginFilteredPluginManager extends FilteredPluginManager {

  /**
   * The payment to filter methods by.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * Creates a new instance.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Another plugin manager that the filters are applied to.
   * @param \Drupal\plugin_selector\Plugin\PluginDefinitionMapperInterface $plugin_definition_mapper
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   */
  public function __construct(PluginManagerInterface $plugin_manager, PluginDefinitionMapperInterface $plugin_definition_mapper, PaymentInterface $payment) {
    parent::__construct($plugin_manager, $plugin_definition_mapper);
    $this->payment = $payment;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $plugin = $this->pluginManager->createInstance($plugin_id, $configuration);
    if ($plugin instanceof PaymentAwareInterface) {
      $plugin->setPayment($this->payment);
    }

    return $plugin;
  }

}
