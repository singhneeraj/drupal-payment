<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase.
 */

namespace Drupal\payment\Plugin\Payment\Status;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base payment status.
 *
 * Plugins extending this class should provide a configuration schema that
 * extends payment.plugin_configuration.payment_status.payment_base.
 */
abstract class PaymentStatusBase extends PluginBase implements ContainerFactoryPluginInterface, PaymentStatusInterface {

  /**
   * The payment this payment status is for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * The payment status plugin manager.
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
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
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
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'created' => time(),
      'id' => 0,
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
  public function setCreated($created) {
    $this->configuration['created'] = $created;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreated() {
    return $this->configuration['created'];
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
  public function setId($id) {
    $this->configuration['id'] = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  function getAncestors(){
    return $this->paymentStatusManager->getAncestors($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    return $this->paymentStatusManager->getChildren($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  function getDescendants() {
    return $this->paymentStatusManager->getDescendants($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  function hasAncestor($plugin_id) {
    return $this->paymentStatusManager->hasAncestor($this->getPluginId(), $plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  function isOrHasAncestor($plugin_id) {
    return $this->paymentStatusManager->isOrHasAncestor($this->getPluginId(), $plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }
}
