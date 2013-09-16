<?php

/**
 * Contains \Drupal\payment\PaymentMethod.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodInterface as EntityPaymentMethodInterface;

/**
 * A base payment method plugin.
 */
abstract class Base extends PluginBase implements PaymentMethodInterface {

  /**
   * The payment method entity this plugin belongs to.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $paymentMethod;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
    $message = check_markup($this->getMessageText(), $this->getMessageTextFormat());
    $message = \Drupal::service('token')->replace($message, array(
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
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    // @todo Add a token overview, possibly when Token.module has been ported.
    $elements['#element_validate'] = array(array($this, 'paymentMethodFormElementsValidate'));
    $elements['#tree'] = TRUE;
    $elements['message'] = array(
      '#type' => 'text_format',
      '#title' => t('Payment form message'),
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
    // Check that the requested brand has any currency information at all.
    if (isset($brands[$payment_method_brand]) && isset($brands[$payment_method_brand]['currencies'])) {
      $currencies = $brands[$payment_method_brand]['currencies'];
      // If no currencies are specified, all currencies are allowed.
      if (empty($currencies)) {
        return TRUE;
      }
      // If the payment's currency is not specified, access is denied.
      elseif (!isset($currencies[$payment->getCurrencyCode()])) {
        return FALSE;
      }
      // Confirm the payment amount is higher than the supported minimum.
      elseif (isset($currencies[$payment->getCurrencyCode()]['minimum']) && $payment->getAmount() < $currencies[$payment->getCurrencyCode()]['minimum']) {
        return FALSE;
      }
      // Confirm the payment amount does not exceed the maximum.
      elseif (isset($currencies[$payment->getCurrencyCode()]['maximum']) && $payment->getAmount() > $currencies[$payment->getCurrencyCode()]['maximum']) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Invokes events for self::executePaymentAccess().
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $payment_method_brand
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function paymentExecuteAccessEvent(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    $handler = \Drupal::moduleHandler();
    foreach ($handler->getImplementations('payment_execute_access') as $module) {
      $module_access = $handler->invoke($module, 'payment_execute_access', $payment, $this->getPaymentMethod(), $payment_method_brand, $account);
      if ($module_access === FALSE) {
        return FALSE;
      }
    }
    // @todo invoke Rules event.
    return TRUE;
  }

  /**
   * Invokes events for self::executePayment().
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return bool
   */
  protected function paymentExecuteEvent(PaymentInterface $payment) {
    $handler = \Drupal::moduleHandler();
    $handler->invokeAll('payment_pre_execute', array($payment));
    // @todo invoke Rules event.
  }
}
