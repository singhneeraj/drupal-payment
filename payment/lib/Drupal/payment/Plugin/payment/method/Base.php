<?php

/**
 * Contains \Drupal\payment\PaymentMethod.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodInterface as EntityPaymentMethodInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base payment method plugin.
 */
abstract class Base extends PluginBase implements AccessInterface, ContainerFactoryPluginInterface, PaymentMethodInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The payment method entity this plugin belongs to.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $paymentMethod;

  /**
   * The token API.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, Token $token) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('module_handler'), $container->get('token'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'message_text' => '',
      'message_text_format' => 'plain_text',
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
    return $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethod(EntityPaymentMethodInterface $payment_method) {
    $this->paymentMethod = $payment_method;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    return $this->paymentMethod;
  }

  /**
   * Sets payer message text.
   *
   * @param string $text
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  public function setMessageText($text) {
    $this->configuration['message_text'] = $text;

    return $this;
  }

  /**
   * Gets the payer message text.
   *
   * @return string
   */
  public function getMessageText() {
    return $this->configuration['message_text'];
  }

  /**
   * Sets payer message text format.
   *
   * @param string $format
   *   The machine name of the text format the payer message is in.
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  public function setMessageTextFormat($format) {
    $this->configuration['message_text_format'] = $format;

    return $this;
  }

  /**
   * Gets the payer message text format.
   *
   * @return string
   */
  public function getMessageTextFormat() {
    return $this->configuration['message_text_format'];
  }

  /**
   * {@inheritdoc}
   */
  public function paymentFormElements(array $form, array &$form_state, PaymentInterface $payment) {
    $message = $this->checkMarkup($this->getMessageText(), $this->getMessageTextFormat());
    $message = $this->token->replace($message, array(
      'payment' => $payment,
    ), array(
      'clear' => TRUE,
    ));
    $elements = array();
    $elements['message'] = array(
      '#type' => 'markup',
      '#markup' => $message,
    );

    return $elements;
  }

  /**
   * Wraps check_markup().
   */
  protected function checkMarkup($text, $format_id = NULL, $langcode = '', $cache = FALSE, $filter_types_to_skip = array()) {
    return check_markup($text, $format_id, $langcode, $cache, $filter_types_to_skip);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    // @todo Add a token overview, possibly when Token.module has been ported.
    $elements['#element_validate'] = array(array($this, 'paymentMethodFormElementsValidate'));
    $elements['#tree'] = TRUE;
    $elements['message'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Payment form message'),
      '#default_value' => $this->getMessageText(),
      '#format' => $this->getMessageTextFormat(),
    );

    return $elements;
  }

  /**
   * Implements form validate callback for self::paymentMethodFormElements().
   */
  public function paymentMethodFormElementsValidate(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setMessageText($values['message']['value']);
    $this->setMessageTextFormat($values['message']['format']);
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    return $this->getPaymentMethod()->status()
      && $this->paymentExecuteAccessCurrency($payment, $payment_method_brand, $account)
      && $this->paymentExecuteAccessEvent($payment, $payment_method_brand, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
    $this->paymentExecuteEvent($payment);
  }

  /**
   * Checks a payment's currency against this plugin.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $payment_method_brand
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function paymentExecuteAccessCurrency(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    $brands = $this->brands();
    $payment_currency_code = $payment->getCurrencyCode();
    $payment_amount = $payment->getAmount();
    // Check that the requested brand exists.
    if (isset($brands[$payment_method_brand])) {
      // If no currencies are specified, all currencies are allowed.
      if (!isset($brands[$payment_method_brand]['currencies'])) {
        return TRUE;
      }

      $currencies = $brands[$payment_method_brand]['currencies'];
      // If the payment's currency is not specified, access is denied.
      if (!isset($currencies[$payment_currency_code])) {
        return FALSE;
      }
      // Confirm the payment amount is higher than the supported minimum.
      elseif (isset($currencies[$payment_currency_code]['minimum']) && $payment_amount < $currencies[$payment_currency_code]['minimum']) {
        return FALSE;
      }
      // Confirm the payment amount does not exceed the maximum.
      elseif (isset($currencies[$payment_currency_code]['maximum']) && $payment_amount > $currencies[$payment_currency_code]['maximum']) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Invokes events for self::executePaymentAccess().
   *
   * @todo Invoke Rules event.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $payment_method_brand
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function paymentExecuteAccessEvent(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    $access = $this->moduleHandler->invokeAll('payment_execute_access', $payment, $this->getPaymentMethod(), $payment_method_brand, $account);

    // If there are no results, grant access.
    return empty($access) || in_array(self::ALLOW, $access, TRUE) && !in_array(self::KILL, $access, TRUE);
  }

  /**
   * Invokes events for self::executePayment().
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return bool
   */
  protected function paymentExecuteEvent(PaymentInterface $payment) {
    $this->moduleHandler->invokeAll('payment_pre_execute', array($payment));
    // @todo invoke Rules event.
  }
}
