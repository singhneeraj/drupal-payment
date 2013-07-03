<?php

/**
 * Contains \Drupal\payment\PaymentMethod.
 */

namespace Drupal\payment\Plugin\payment\method;

use Doctrine\Tests\Common\Annotations\False;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;
use Drupal\payment\Plugin\Core\entity\PaymentInterface;

/**
 * A base payment method controller.
 */
abstract class Base extends PluginBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += array(
      'messageText' => '',
      'messageTextFormat' => '',
      'paymentMethod' => NULL,
    );
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
  public function getPaymentMethod() {
    return $this->configuration['paymentMethod'];
  }

  /**
   * Sets payer message text.
   *
   * @param string $text
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentInterface
   */
  public function setMessageText($text) {
    $this->configuration['messageText'] = $text;

    return $this;
  }

  /**
   * Gets the payer message text.
   *
   * @return string
   */
  public function getMessageText() {
    return $this->configuration['messageText'];
  }

  /**
   * Sets payer message text format.
   *
   * @param string $format
   *   The machine name of the text format the payer message is in.
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentInterface
   */
  public function setMessageTextFormat($format) {
    $this->configuration['messageTextFormat'] = $format;

    return $this;
  }

  /**
   * Gets the payer message text format.
   *
   * @return string
   */
  public function getMessageTextFormat() {
    return $this->configuration['messageTextFormat'];
  }

  /**
   * {@inheritdoc}
   */
  public function paymentFormElements(array $form, array &$form_state) {
    $elements = array();
    $elements['message'] = array(
      '#type' => 'markup',
      '#markup' => check_markup($this->getMessageText(), $this->getMessageTextFormat()),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    $elements['message'] = array(
      '#element_validate' => array($this, 'PaymentMethodFormElementsValidateMessage'),
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
  public function PaymentMethodFormElementsValidateMessage(array $element, array &$form_state, array $form) {
    $values = drupal_array_get_nested_value($form_state['values'], $element['#parents']);
    $this->setMessageText($values['message']['value']);
    $this->setMessageTextFormat($values['message']['format']);
  }

  /**
   * {@inheritdoc}
   */
  function paymentOperationAccess(PaymentInterface $payment, $operation) {
    if (!$this->getPaymentMethod()->status()) {
      return FALSE;
    }
    if (!$this->paymentOperationAccessCurrency($payment, $operation)) {
      return FALSE;
    }
    if (!$this->paymentOperationAccessEvent($payment, $operation)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Checks a payment's currency against this plugin.
   */
  protected function paymentOperationAccessCurrency(PaymentInterface $payment, $operation) {
    if (!$payment->getCurrencyCode()) {
      return FALSE;
    }
    $currencies = $this->currencies();
    // Confirm the payment's currency is supported.
    $currencies = $this->currencies();
    if (!empty($currencies) && !isset($currencies[$payment->getCurrencyCode()])) {
      return FALSE;
    }
    // Confirm the payment amount is higher than the supported minimum.
    if (isset($currencies[$payment->getCurrencyCode()]['minimum']) && $payment->getAmount() < $currencies[$payment->getCurrencyCode()]['minimum']) {
      return FALSE;
    }
    // Confirm the payment amount does not exceed the maximum.
    if (isset($currencies[$payment->getCurrencyCode()]['maximum']) && $payment->getAmount() > $currencies[$payment->getCurrencyCode()]['maximum']) {
      return FALSE;
    }
  }

  /**
   * Invokes events for self::paymentOperationAccess().
   *
   * @param \Drupal\payment\Plugin\Core\entity\PaymentInterface $payment
   * @param string $operation
   *
   * @return bool
   */
  protected function paymentOperationAccessEvent(PaymentInterface $payment, $operation) {
    $handler = \Drupal::moduleHandler();
    foreach ($handler->getImplementations('payment_operation_access') as $module) {
      $module_access = $handler->invoke($module, 'payment_operation_access', $payment, $this->getPaymentMethod(), $operation);
      if ($module_access === FALSE) {
        return FALSE;
      }
    }
    // @todo invoke Rules event.

    return TRUE;
  }
}
