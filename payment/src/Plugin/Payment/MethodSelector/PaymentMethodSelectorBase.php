<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base payment method selector plugin.
 */
abstract class PaymentMethodSelectorBase extends PluginBase implements ContainerFactoryPluginInterface, PaymentMethodSelectorInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The payment a payment method is selected for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The selected payment method.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  protected $selectedPaymentMethod;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountInterface $current_user, PaymentMethodManagerInterface $payment_method_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('current_user'), $container->get('plugin.manager.payment.method'));
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
      'allowed_payment_method_plugin_ids' => NULL,
      'required' => FALSE,
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

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAllowedPaymentMethods($payment_method_plugin_ids) {
    $this->configuration['allowed_payment_method_plugin_ids'] = $payment_method_plugin_ids;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function resetAllowedPaymentMethods() {
    $this->configuration['allowed_payment_method_plugin_ids'] = TRUE;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedPaymentMethods() {
    return $this->configuration['allowed_payment_method_plugin_ids'];
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired($required = TRUE) {
    $this->configuration['required'] = $required;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired() {
    return $this->configuration['required'];
  }

  /**
   * Returns all available payment methods.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[]
   *    An array of payment method plugin instances, keyed by plugin ID.
   */
  protected function getAvailablePaymentMethods() {
    $payment_methods = array();
    foreach (array_keys($this->paymentMethodManager->getDefinitions()) as $plugin_id) {
      if (is_null($this->getAllowedPaymentMethods()) || in_array($plugin_id, $this->getAllowedPaymentMethods())) {
        $payment_method = $this->paymentMethodManager->createInstance($plugin_id);
        $payment_method->setPayment($this->getPayment());
        if ($payment_method->executePaymentAccess($this->currentUser)) {
          $payment_methods[$payment_method->getPluginId()] = $payment_method;
        }
      }
    }

    return $payment_methods;
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
  public function getPaymentMethod() {
    return $this->selectedPaymentMethod;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
  }

}
