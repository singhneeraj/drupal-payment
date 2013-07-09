<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Basic.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Plugin\Core\Entity\PaymentInterface;

/**
 * A basic payment method that does not transfer money.
 *
 * @PaymentMethod(
 *   description = "A 'dumb' payment method type that always successfully executes payments, but never actually transfers money. It can be useful for <em>collect on delivery</em>, for instance.",
 *   id = "payment_basic",
 *   label = @Translation("Basic"),
 *   module = "payment",
 *   operations = {
 *     "execute" = {
 *       "interrupts_execution" = "false",
 *       "label" = @Translation("Execute")
 *     }
 *   }
 * )
 */
class Basic extends Base {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += array(
      'brandOption' => '',
      'status' => '',
    );
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Sets the final payment status.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentInterface
   */
  public function setStatus($status) {
    $this->configuration['status'] = $status;

    return $this;
  }

  /**
   * Gets the final payment status.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getStatus() {
    return $this->configuration['status'];
  }

  /**
   * {@inheritdoc}.
   */
  public function currencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    $elements = parent::paymentMethodFormElements($form, $form_state);
    $elements['#element_validate'][] = array($this, 'paymentMethodFormElementsValidateBasic');

    $elements['brand'] = array(
      '#default_value' => $this->configuration['brandOption'],
      '#description' => t('The label that payers will see when choosing a payment method. Defaults to the payment method label.'),
      '#title' => t('Brand label'),
      '#type' => 'textfield',
    );
    $elements['status'] = array(
      '#type' => 'select',
      '#title' => t('Final payment status'),
      '#description' => t('The status to give a payment after being processed by this payment method.'),
      '#default_value' => $this->getStatus() ? $this->getStatus() : 'payment_success',
      '#options' => \Drupal::service('plugin.manager.payment.status')->options(),
    );

    return $elements;
  }

  /**
   * Implements form validate callback for self::paymentMethodFormElements().
   */
  public function paymentMethodFormElementsValidateBasic(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setStatus($values['status'])
      ->setBrandOption($values['brand']);
  }

  /**
   * {@inheritdoc}
   */
  function paymentOperationAccess(PaymentInterface $payment, $operation, $payment_method_brand) {
    // This plugin only supports the execute operation.
    return $operation == 'execute' && parent::paymentOperationAccess($payment, $operation, $payment_method_brand);
  }

  /**
   * {@inheritdoc}
   */
  protected function paymentOperationAccessCurrency(PaymentInterface $payment, $operation, $payment_method_brand) {
    // This plugin supports any currency.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function executePaymentOperation(PaymentInterface $payment, $operation, $payment_method_brand) {
    if ($this->paymentOperationAccess($payment, $operation, $payment_method_brand)) {
      if ($operation == 'execute') {
        $payment->setStatus(\Drupal::service('plugin.manager.payment.status')->createInstance($this->getStatus()));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function brandOptions() {
    return array(
      'default' => $this->configuration['brandOption'] ? $this->configuration['brandOption'] : $this->getPaymentMethod()->label(),
    );
  }

  /**
   * Sets the brand option label.
   *
   * @param string $label
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  public function setBrandOption($label) {
    $this->configuration['brandOption'] = $label;

    return $this;
  }
}
