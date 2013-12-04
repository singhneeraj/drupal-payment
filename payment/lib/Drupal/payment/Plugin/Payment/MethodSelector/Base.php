<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodSelector\Base.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\Manager as PaymentMethodManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base payment method selector plugin.
 */
abstract class Base extends PluginBase implements ContainerFactoryPluginInterface, PaymentMethodSelectorInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager
   */
  protected $paymentMethodManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\payment\Plugin\Payment\Method\Manager $payment_method_manager
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountInterface $current_user, PaymentMethodManager $payment_method_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('current_user'), $container->get('plugin.manager.payment.method'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'allowed_payment_method_plugin_ids' => NULL,
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
  public function setAllowedPaymentMethods(array $payment_method_plugin_ids) {
    $this->configuration['allowed_payment_method_plugin_ids'] = $payment_method_plugin_ids;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function resetAllowedPaymentMethods() {
    $this->configuration['allowed_payment_method_plugin_ids'] = NULL;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedPaymentMethods() {
    return $this->configuration['allowed_payment_method_plugin_ids'];
  }

  /**
   * Returns all available payment methods for a Payment.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[]
   *    An array of payment method plugin instances, keyed by plugin ID.
   */
  protected function getAvailablePaymentMethods(PaymentInterface $payment) {
    $payment_methods = array();
    foreach (array_keys($this->paymentMethodManager->getDefinitions()) as $plugin_id) {
      if (is_null($this->getAllowedPaymentMethods()) || in_array($plugin_id, $this->getAllowedPaymentMethods())) {
        $payment_method = $this->paymentMethodManager->createInstance($plugin_id);
        if ($payment_method->executePaymentAccess($payment, $this->currentUser)) {
          $payment_methods[$payment_method->getPluginId()] = $payment_method;
        }
      }
    }

    return $payment_methods;
  }
}
