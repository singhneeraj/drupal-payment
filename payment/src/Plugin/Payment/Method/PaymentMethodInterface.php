<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Defines a payment method.
 *
 * PluginFormInterface is used to configure the plugin for a payment. The form
 * is embedded in another form, so self::submitForm() must only save form
 * values to $this and not redirect the page, for instance.
 */
interface PaymentMethodInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Checks if the payment can be executed.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  public function executePaymentAccess(AccountInterface $account);

  /**
   * Executes the payment.
   */
  public function executePayment();

  /**
   * Gets the payment this payment method is for.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment();

  /**
   * Gets the payment this payment method is for.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return $this
   */
  public function setPayment(PaymentInterface $payment);

  /**
   * Gets the plugin label.
   *
   * @return string
   */
  public function getPluginLabel();
}
