<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic.
 */

namespace Drupal\payment\Plugin\Payment\MethodConfiguration;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration for the payment_basic payment method plugin.
 *
 * @PaymentMethodConfiguration(
 *   description = @Translation("A payment method type that always successfully executes payments, but never actually transfers money."),
 *   id = "payment_basic",
 *   label = @Translation("Basic")
 * )
 */
class Basic extends PaymentMethodConfigurationBase implements ContainerFactoryPluginInterface {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'brand_label' => '',
      'status' => '',
    );
  }

  /**
   * Sets the final payment status.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic
   */
  public function setStatus($status) {
    $this->configuration['status'] = $status;

    return $this;
  }

  /**
   * Gets the final payment status.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getStatus() {
    return $this->configuration['status'];
  }

  /**
   * {@inheritdoc}
   */
  public function formElements(array $form, array &$form_state) {
    $elements = parent::formElements($form, $form_state);
    $elements['#element_validate'][] = array($this, 'formElementsValidateBasic');

    $elements['brand_label'] = array(
      '#default_value' => $this->getBrandLabel(),
      '#description' => $this->t('The label that payers will see when choosing a payment method. Defaults to the payment method label.'),
      '#title' => $this->t('Brand label'),
      '#type' => 'textfield',
    );
    $elements['status'] = array(
      '#type' => 'select',
      '#title' => $this->t('Final payment status'),
      '#description' => $this->t('The status to set payments to after being processed by this payment method.'),
      '#default_value' => $this->getStatus() ? $this->getStatus() : 'payment_success',
      '#options' => $this->paymentStatusManager->options(),
    );

    return $elements;
  }

  /**
   * Implements form validate callback for self::formElements().
   */
  public function formElementsValidateBasic(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setStatus($values['status'])
      ->setBrandLabel($values['brand_label']);
  }

  /**
   * Gets the brand label.
   *
   * @return string
   */
  public function getBrandLabel() {
    return$this->configuration['brand_label'];
  }

  /**
   * Sets the brand label.
   *
   * @param string $label
   *
   * @return static
   */
  public function setBrandLabel($label) {
    $this->configuration['brand_label'] = $label;

    return $this;
  }
}
