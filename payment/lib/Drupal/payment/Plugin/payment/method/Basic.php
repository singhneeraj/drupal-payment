<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Basic.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Plugin\Core\entity\PaymentInterface;

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
    $elements['status'] = array(
      '#element_validate' => array($this, 'paymentMethodFormElementsValidateStatus'),
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
  public function paymentMethodFormElementsValidateStatus(array $element, array &$form_state, array $form) {
    $values = drupal_array_get_nested_value($form_state['values'], $element['#parents']);
    $this->setStatus($values['status']);
  }

  /**
   * {@inheritdoc}
   */
  function paymentOperationAccess(PaymentInterface $payment, $operation) {
    // This plugin only supports the execute operation.
    return $operation == 'execute' && parent::paymentOperationAccess($payment, $operation);
  }

  /**
   * {@inheritdoc}
   */
  protected function paymentOperationAccessCurrency(PaymentInterface $payment, $operation) {
    // This plugin supports any currency.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function executePaymentOperation(PaymentInterface $payment, $operation) {
    if ($this->paymentOperationAccess($payment, $operation)) {
      if ($operation == 'execute') {
        $payment->setStatus(\Drupal::service('plugin.manager.payment.status')->createInstance($this->getStatus()));
      }
    }
  }
}
