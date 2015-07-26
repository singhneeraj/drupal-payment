<?php

/**
 * @file
 * Contains Drupal\payment\Plugin\views\filter\PaymentMethod.
 */

namespace Drupal\payment\Plugin\views\filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a payment method filter.
 *
 * @ViewsFilter("payment_method")
 */
class PaymentMethod extends InOperator implements ContainerFactoryPluginInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, TranslationInterface $string_translation, PaymentMethodManagerInterface $payment_method_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->paymentMethodManager = $payment_method_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $container->get('plugin.manager.payment.method'));
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueTitle = $this->t('Payment method');
      foreach ($this->paymentMethodManager->getDefinitions() as $plugin_id => $definition) {
        $this->valueOptions[$plugin_id] = $definition['label'];
      }
    }

    return $this->valueOptions;
  }

}
