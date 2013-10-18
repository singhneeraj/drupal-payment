<?php

/**
 * Contains \Drupal\payment_form\Plugin\payment\type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\payment\type;

use Drupal\payment\Plugin\payment\type\Base;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The payment form field payment type.
 *
 * @PaymentType(
 *   id = "payment_form",
 *   label = @Translation("Payment form field")
 * )
 */
class PaymentForm extends Base {

  /**
   * {@inheritdoc}
   */
  public function resumeContext() {
    parent::resumeContext();

    return new RedirectResponse(url('<front>', array(
      'absolute' => TRUE,
    )));

  }

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    // @todo Test this.
    $instance_id = $this->getPayment()->payment_form_field_instance;
    $instance = entity_load('field_instance', $instance_id);

    return $instance->label();
  }
}
