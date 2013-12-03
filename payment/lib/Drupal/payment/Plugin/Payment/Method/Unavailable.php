<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\Unavailable.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodInterface as EntityPaymentMethodInterface;

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
  protected function currencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(PaymentInterface $payment, AccountInterface $account = NULL) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
    throw new \RuntimeException('This plugin cannot execute payments.');
  }

  /**
   * {@inheritdoc}
   */
  public function formElements(array $form, array &$form_state, PaymentInterface $payment) {
    return array();
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
  public static function getOperations($plugin_id) {
    return array();
  }
}
