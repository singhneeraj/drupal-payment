<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\FilteredPaymentMethodManager.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\plugin\Plugin\DefaultPluginDefinitionMapper;
use Drupal\payment\Plugin\Payment\PaymentAwarePluginFilteredPluginManager;

/**
 * Provides a filtered payment method plugin manager.
 *
 * @see \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
 */
class FilteredPaymentMethodManager extends PaymentAwarePluginFilteredPluginManager implements PaymentMethodManagerInterface {

  /**
   * The account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Creates a new instance.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\payment\Entity\PaymentInterface
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(PaymentMethodManagerInterface $payment_method_manager, PaymentInterface $payment, AccountInterface $account) {
    parent::__construct($payment_method_manager, new DefaultPluginDefinitionMapper(), $payment);
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  protected function filterDefinition(array $plugin_definition) {
    if (!parent::filterDefinition($plugin_definition)) {
      return FALSE;
    }

    $payment_method = $this->createInstance($this->pluginDefinitionMapper->getPluginId($plugin_definition));

    return $payment_method->executePaymentAccess($this->account);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperationsProvider($plugin_id) {
    if ($this->hasDefinition($plugin_id)) {
      /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager */
      $payment_method_manager = $this->pluginManager;
      return $payment_method_manager->getOperationsProvider($plugin_id);
    }
    else {
      throw new PluginNotFoundException($plugin_id);
    }
  }

}
