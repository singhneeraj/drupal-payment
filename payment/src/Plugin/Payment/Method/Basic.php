<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\Basic.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
   * The payment status manager.
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
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EventDispatcherInterface $event_dispatcher, Token $token, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $token);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('event_dispatcher'), $container->get('token'), $container->get('plugin.manager.payment.status'));
  }

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
    $this->getPayment()->setStatus($this->paymentStatusManager->createInstance($this->getExecuteStatusId()));
    $this->getPayment()->save();
    $this->getPayment()->getPaymentType()->resumeContext();
  }

  /**
   * {@inheritdoc}
   */
  public function doCapturePayment() {
    $this->getPayment()->setStatus($this->paymentStatusManager->createInstance($this->getCaptureStatusId()));
    $this->getPayment()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function doCapturePaymentAccess(AccountInterface $account) {
    return $this->getCapture() && $this->getPayment()->getStatus()->getPluginId() == $this->getExecuteStatusId();
  }

  /**
   * {@inheritdoc}
   */
  public function doRefundPayment() {
    $this->getPayment()->setStatus($this->paymentStatusManager->createInstance($this->getRefundStatusId()));
    $this->getPayment()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function doRefundPaymentAccess(AccountInterface $account) {
    return $this->getRefund() && $this->getPayment()->getStatus()->getPluginId() == $this->getCaptureStatusId();
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
    return array();
  }

}
