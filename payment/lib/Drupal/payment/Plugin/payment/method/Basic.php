<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Basic.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Payment;

/**
 * A basic payment method that does not transfer money.
 *
 * @PaymentMethod(
 *   description = @Translation("A payment method type that always successfully executes payments, but never actually transfers money."),
 *   id = "payment_basic",
 *   label = @Translation("Basic"),
 *   module = "payment"
 * )
 */
class Basic extends Base {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'brand_option' => '',
      'status' => '',
    );
  }

  /**
   * Sets the final payment status.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return \Drupal\payment\Plugin\payment\method\Basic
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
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    $elements = parent::paymentMethodFormElements($form, $form_state);
    $elements['#element_validate'][] = array($this, 'paymentMethodFormElementsValidateBasic');

    $elements['brand'] = array(
      '#default_value' => $this->configuration['brand_option'],
      '#description' => t('The label that payers will see when choosing a payment method. Defaults to the payment method label.'),
      '#title' => t('Brand label'),
      '#type' => 'textfield',
    );
    $elements['status'] = array(
      '#type' => 'select',
      '#title' => t('Final payment status'),
      '#description' => t('The status to give a payment after being processed by this payment method.'),
      '#default_value' => $this->getStatus() ? $this->getStatus() : 'payment_success',
      '#options' => Payment::statusManager()->options(),
    );

    return $elements;
  }

  /**
   * Implements form validate callback for self::paymentMethodFormElements().
   */
  public function paymentMethodFormElementsValidateBasic(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setStatus($values['status'])
      ->setBrandLabel($values['brand']);
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    return $payment_method_brand == 'default' && parent::executePaymentAccess($payment, $payment_method_brand, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
    if ($this->executePaymentAccess($payment, $payment->getPaymentMethodBrand())) {
      $payment->setStatus(Payment::statusManager()->createInstance($this->getStatus()));
      $payment->save();
    }
    $payment->getPaymentType()->resumeContext();
  }

  /**
   * {@inheritdoc}
   */
  public function brands() {
    return array(
      'default' => array(
        'currencies' => array(),
        'label' => $this->configuration['brand_option'] ? $this->configuration['brand_option'] : $this->getPaymentMethod()->label(),
      ),
    );
  }

  /**
   * Sets the brand option label.
   *
   * @param string $label
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  public function setBrandLabel($label) {
    $this->configuration['brand_option'] = $label;

    return $this;
  }
}
