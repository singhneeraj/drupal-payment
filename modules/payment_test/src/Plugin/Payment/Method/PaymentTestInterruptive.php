<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodBase;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test_interruptive",
 *   label = @Translation("Test method (interruptive)"),
 *   message_text = "Foo",
 *   message_text_format = "plain_text"
 * )
 */
class PaymentTestInterruptive extends PaymentMethodBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, EventDispatcherInterface $event_dispatcher, Token $token, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $event_dispatcher, $token);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('module_handler'), $container->get('event_dispatcher'), $container->get('token'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getSupportedCurrencies() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function doExecutePayment() {
    $this->getPayment()->setPaymentStatus($this->paymentStatusManager->createInstance('payment_success'));
    $this->getPayment()->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function doCapturePayment() {
  }

  /**
   * {@inheritdoc}
   */
  protected function doCapturePaymentAccess(AccountInterface $account) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function doRefundPayment() {
  }

  /**
   * {@inheritdoc}
   */
  protected function doRefundPaymentAccess(AccountInterface $account) {
    return FALSE;
  }

}
