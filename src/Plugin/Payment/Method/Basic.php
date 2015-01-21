<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\Basic.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A basic payment method that does not transfer money.
 *
 * Plugins that extend this class must have the following keys in their plugin
 * definitions:
 * - entity_id: (string) The ID of the payment method entity the plugin is for.
 * - execute_status_id: (string) The ID of the payment status plugin to set at
 *   payment execution.
 * - capture: (boolean) Whether or not payment capture is supported.
 * - capture_status_id: (string) The ID of the payment status plugin to set at
 *   payment capture.
 * - refund: (boolean) Whether or not payment refunds are supported.
 * - refund_status_id: (string) The ID of the payment status plugin to set at
 *   payment refund.
 *
 * @PaymentMethod(
 *   deriver = "Drupal\payment\Plugin\Payment\Method\BasicDeriver",
 *   id = "payment_basic",
 *   operations_provider = "\Drupal\payment\Plugin\Payment\Method\BasicOperationsProvider",
 * )
 */
class Basic extends PaymentMethodBase implements ContainerFactoryPluginInterface, PaymentMethodCapturePaymentInterface, PaymentMethodRefundPaymentInterface, PaymentMethodUpdatePaymentStatusInterface {

  /**
   * {@inheritdoc}
   */
  public function getSupportedCurrencies() {
    return TRUE;
  }

  /**
   * Gets the ID of the payment method this plugin is for.
   *
   * @return string
   */
  public function getEntityId() {
    return $this->pluginDefinition['entity_id'];
  }

  /**
   * Gets the status to set on payment execution.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getExecuteStatusId() {
    return $this->pluginDefinition['execute_status_id'];
  }

  /**
   * Gets the status to set on payment capture.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getCaptureStatusId() {
    return $this->pluginDefinition['capture_status_id'];
  }

  /**
   * Gets whether or not capture is supported.
   *
   * @param bool
   *   Whether or not to support capture.
   */
  public function getCapture() {
    return $this->pluginDefinition['capture'];
  }

  /**
   * Gets the status to set on payment refund.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getRefundStatusId() {
    return $this->pluginDefinition['refund_status_id'];
  }

  /**
   * Gets whether or not capture is supported.
   *
   * @param bool
   *   Whether or not to support capture.
   */
  public function getRefund() {
    return $this->pluginDefinition['refund'];
  }

  /**
   * {@inheritdoc}
   */
  protected function doExecutePayment() {
    $this->getPayment()->setPaymentStatus($this->paymentStatusManager->createInstance($this->getExecuteStatusId()));
    $this->getPayment()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function doCapturePayment() {
    $this->getPayment()->setPaymentStatus($this->paymentStatusManager->createInstance($this->getCaptureStatusId()));
    $this->getPayment()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function doCapturePaymentAccess(AccountInterface $account) {
    return $this->getCapture() && $this->getPayment()->getPaymentStatus()->getPluginId() == $this->getExecuteStatusId();
  }

  /**
   * {@inheritdoc}
   */
  public function doRefundPayment() {
    $this->getPayment()->setPaymentStatus($this->paymentStatusManager->createInstance($this->getRefundStatusId()));
    $this->getPayment()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function doRefundPaymentAccess(AccountInterface $account) {
    return $this->getRefund() && $this->getPayment()->getPaymentStatus()->getPluginId() == $this->getCaptureStatusId();
  }

  /**
   * {@inheritdoc}
   */
  public function updatePaymentStatusAccess(AccountInterface $account) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettablePaymentStatuses(AccountInterface $account, PaymentInterface $payment) {
    return [];
  }

}
