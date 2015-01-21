<?php

/**
 * Contains \Drupal\payment\PaymentMethod\PaymentMethodBase.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\PaymentExecutionResult;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base payment method plugin.
 *
 * Plugins that extend this class must have the following two keys in their
 * plugin definitions:
 * - message_text: The translated human-readable text to display in the payment
 *   form.
 * - message_text_format: The ID of the text format to format message_text with.
 */
abstract class PaymentMethodBase extends PluginBase implements ContainerFactoryPluginInterface, PaymentMethodInterface, PaymentMethodCapturePaymentInterface, PaymentMethodRefundPaymentInterface {

  /**
   * The event dispatcher.
   *
   * @var \Drupal\payment\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The payment this payment method is for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The token API.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

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
   * @param \Drupal\payment\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, EventDispatcherInterface $event_dispatcher, Token $token, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventDispatcher = $event_dispatcher;
    $this->moduleHandler = $module_handler;
    $this->paymentStatusManager = $payment_status_manager;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('module_handler'), $container->get('payment.event_dispatcher'), $container->get('token'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
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
  }

  /**
   * Gets the payer message text.
   *
   * @return string
   */
  public function getMessageText() {
    return $this->pluginDefinition['message_text'];
  }

  /**
   * Gets the payer message text format.
   *
   * @return string
   */
  public function getMessageTextFormat() {
    return $this->pluginDefinition['message_text_format'];
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $message_text = $this->token->replace($this->getMessageText(), array(
      'payment' => $this->getPayment(),
    ), array(
      'clear' => TRUE,
    ));
    if ($this->moduleHandler->moduleExists('filter')) {
      $elements['message'] = array(
        '#type' => 'processed_text',
        '#text' => $message_text,
        '#format' => $this->getMessageTextFormat(),
      );
    }
    else {
      $elements['message'] = array(
        '#type' => 'markup',
        '#markup' => $message_text,
      );
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(AccountInterface $account) {
    if (!$this->getPayment()) {
      throw new \LogicException('Trying to check access for a non-existing payment. A payment must be set trough self::setPayment() first.');
    }

    return $this->pluginDefinition['active']
    && $this->executePaymentAccessCurrency($account)
    && $this->eventDispatcher->executePaymentAccess($this->getPayment(), $this, $account)->isAllowed()
    && $this->doExecutePaymentAccess($account);
  }

  /**
   * Performs a payment method-specific access check for payment execution.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function doExecutePaymentAccess(AccountInterface $account) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment() {
    if (!$this->getPayment()) {
      throw new \LogicException('Trying to execute a non-existing payment. A payment must be set trough self::setPayment() first.');
    }
    $this->eventDispatcher->preExecutePayment($this->getPayment());
    $this->payment->setPaymentStatus($this->paymentStatusManager->createInstance('payment_pending'));
    $this->doExecutePayment();
    $this->getPayment()->save();

    return $this->getPaymentExecutionResult();
  }

  /**
   * Performs the actual payment execution.
   */
  protected function doExecutePayment() {
    // This method is empty so child classes can override it and provide their
    // own implementation.
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentExecutionResult() {
    return new PaymentExecutionResult();
  }

  /**
   * {@inheritdoc}
   */
  public function capturePaymentAccess(AccountInterface $account) {
    if (!$this->getPayment()) {
      throw new \LogicException('Trying to check access for a non-existing payment. A payment must be set trough self::setPayment() first.');
    }

    return $this->doCapturePaymentAccess($account);
  }

  /**
   * Performs a payment method-specific access check for payment capture.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function doCapturePaymentAccess(AccountInterface $account) {
    // Child classes must override this method to support payment capture.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment() {
    if (!$this->getPayment()) {
      throw new \LogicException('Trying to capture a non-existing payment. A payment must be set trough self::setPayment() first.');
    }
    $this->eventDispatcher->preCapturePayment($this->getPayment());
    $this->doCapturePayment();
  }

  /**
   * Performs the actual payment capture.
   */
  protected function doCapturePayment() {
    throw new \Exception('Child classes must override this method to support payment capture.');
  }

  /**
   * {@inheritdoc}
   */
  public function refundPaymentAccess(AccountInterface $account) {
    if (!$this->getPayment()) {
      throw new \LogicException('Trying to check access for a non-existing payment. A payment must be set trough self::setPayment() first.');
    }

    return $this->doRefundPaymentAccess($account);
  }

  /**
   * Performs a payment method-specific access check for payment refunds.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function doRefundPaymentAccess(AccountInterface $account) {
    // Child classes must override this method to support payment refund.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function refundPayment() {
    if (!$this->getPayment()) {
      throw new \LogicException('Trying to refund a non-existing payment. A payment must be set trough self::setPayment() first.');
    }
    $this->eventDispatcher->preRefundPayment($this->getPayment());
    $this->doRefundPayment();
  }

  /**
   * Performs the actual payment refund.
   */
  protected function doRefundPayment() {
    throw new \Exception('Child classes must override this method to support payment refund.');
  }

  /**
   * Checks a payment's currency against this plugin.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function executePaymentAccessCurrency(AccountInterface $account) {
    $supported_currencies = $this->getSupportedCurrencies();
    $payment_currency_code = $this->getPayment()->getCurrencyCode();
    $payment_amount = $this->getPayment()->getAmount();
    // If all currencies are allowed, grant access.
    if ($supported_currencies === TRUE) {
      return TRUE;
    }
    // If the payment's currency is not specified, access is denied.
    foreach ($supported_currencies as $supported_currency) {
      if ($supported_currency->getCurrencyCode() != $payment_currency_code) {
        continue;
      }
      // Confirm the payment amount is higher than the supported minimum.
      elseif ($supported_currency->getMinimumAmount() && $payment_amount < $supported_currency->getMinimumAmount()) {
        return FALSE;
      }
      // Confirm the payment amount does not exceed the maximum.
      elseif ($supported_currency->getMaximumAmount() && $payment_amount > $supported_currency->getMaximumAmount()) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns the supported currencies.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\SupportedCurrencyInterface[]|true
   *   Return TRUE to allow all currencies and amounts.
   */
  abstract protected function getSupportedCurrencies();

  /**
   * {@inheritdoc}
   */
  public function getPluginLabel() {
    return $this->pluginDefinition['label'];
  }

}
