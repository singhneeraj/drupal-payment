<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\Unavailable.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A payment method controller that essentially disables payment methods.
 *
 * This is a 'placeholder' controller that returns defaults and doesn't really
 * do anything else. It is used when no working controller is available for a
 * payment method, so other modules don't have to check for that.
 *
 * @PaymentMethod(
 *   active = FALSE,
 *   id = "payment_unavailable",
 *   label = @Translation("Unavailable"),
 *   module = "payment"
 * )
 */
class Unavailable extends PluginBase implements PaymentMethodInterface {

  /**
   * The payment this payment method is for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

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
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSupportedCurrencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(AccountInterface $account) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment() {
    throw new \RuntimeException('This plugin cannot execute payments.');
  }

  /**
   * Gets the payment this payment method is for.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment() {
    return $this->payment;
  }

  /**
   * Gets the payment this payment method is for.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return $this
   */
  public function setPayment(PaymentInterface $payment) {
    $this->payment = $payment;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    return array();
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
