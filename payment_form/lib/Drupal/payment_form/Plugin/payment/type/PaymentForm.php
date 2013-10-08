<?php

/**
 * Contains \Drupal\payment_form\Plugin\payment\type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\payment\type;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentType;
use Drupal\payment\Plugin\payment\type\Base;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The payment form field payment type.
 *
 * @PaymentType(
 *   description = @Translation("An instance of the payment form field."),
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

  /**
   * {@inheritdoc
   */
  public static function getOperations($plugin_id) {
    $operations = array();
    if (\Drupal::moduleHandler()->moduleExists('field_ui')) {
      $instance_id = substr($plugin_id, 13);
      $instance = \Drupal::entityManager()->getStorageController('field_instance')->load($instance_id);
      $uri = $instance->uri();
      $operations['payment_form_field_edit'] = array(
        'href' => $uri['path'],
        'options' => $uri['options'],
        'title' => t('Edit payment form field'),
      );
    }

    return $operations;
  }
}
