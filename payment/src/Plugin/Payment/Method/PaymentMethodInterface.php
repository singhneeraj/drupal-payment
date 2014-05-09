<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A payment method plugin (the logic behind a payment method entity).
 *
 * @see \Drupal\payment\Entity\PaymentMethod
 */
interface PaymentMethodInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the form elements to configure payments.
   *
   * $form_state['payment'] contains the payment that is added or edited. All
   * payment-specific information should be added to it during element
   * validation. The payment will be saved automatically.
   *
   * @param array $form
   * @param array $form_state
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return array
   *   A render array.
   */
  public function formElements(array $form, array &$form_state, PaymentInterface $payment);

  /**
   * Checks if a payment can be executed.
   *
   * @see \Drupal\payment\Annotations\PaymentMethod
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  public function executePaymentAccess(PaymentInterface $payment, AccountInterface $account);

  /**
   * Executes a payment.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   */
  public function executePayment(PaymentInterface $payment);

  /**
   * Gets the plugin label.
   *
   * @return string
   */
  public function getPluginLabel();
}
