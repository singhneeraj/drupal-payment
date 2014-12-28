<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase.
 */

namespace Drupal\payment\Plugin\Payment\LineItem;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\currency\Math\MathInterface;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base line item.
 *
 * Plugins extending this class should provide a configuration schema that
 * extends payment.plugin_configuration.line_item.payment_base.
 */
abstract class PaymentLineItemBase extends PluginBase implements PaymentLineItemInterface, ContainerFactoryPluginInterface {

  /**
   * The math service.
   *
   * @var \Drupal\currency\Math\MathInterface
   */
  protected $math;

  /**
   * The payment this line item is for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\currency\Math\MathInterface $math
   *   The math service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MathInterface $math) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->math = $math;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('currency.math'));
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'name' => NULL,
      'quantity' => 1,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getPayment() {
    return $this->payment;
  }

  /**
   * {@inheritdoc}
   */
  public function setPayment(PaymentInterface $payment) {
    $this->payment = $payment;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function getTotalAmount() {
    return $this->math->multiply($this->getAmount(), $this->getQuantity());
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->configuration['name'] = $name;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->configuration['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function setQuantity($quantity) {
    $this->configuration['quantity'] = $quantity;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuantity() {
    return $this->configuration['quantity'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
