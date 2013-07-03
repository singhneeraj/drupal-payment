<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Unavailable.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Plugin\Core\entity\PaymentInterface;
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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += array(
      'paymentMethod' => NULL,
    );
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    return $this->configuration['paymentMethod'];
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
  function paymentOperationAccess(PaymentInterface $payment, $operation) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  function executePaymentOperation(PaymentInterface $payment, $operation) {
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
  public function paymentFormElements(array $form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    return array();
  }
}
