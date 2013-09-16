<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Unavailable.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodInterface as EntityPaymentMethodInterface;
use Drupal\payment\Plugin\payment\method\PaymentMethodInterface;

/**
 * A payment method controller that essentially disables payment methods.
 *
 * This is a 'placeholder' controller that returns defaults and doesn't really
 * do anything else. It is used when no working controller is available for a
 * payment method, so other modules don't have to check for that.
 *
 * @PaymentMethod(
 *   id = "payment_unavailable",
 *   label = @Translation("Unavailable"),
 *   module = "payment"
 * )
 */
class Unavailable extends PluginBase implements PaymentMethodInterface {

  /**
   * The payment method entity this plugin belongs to.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $paymentMethod;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
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
   * {@inheritdoc}.
   */
  public function currencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
  }

  /**
   * {@inheritdoc}
   */
  public function paymentFormElements(array $form, array &$form_state, PaymentInterface $payment) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function brands() {
    $definition = $this->getPluginDefinition();

    return array(
      'default' => array(
        'label' => $definition['label'],
      ),
    );
  }
}
