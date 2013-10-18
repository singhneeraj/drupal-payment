<?php

/**
 * @file
 * Contains \Drupal\payment_form\Plugin\field\formatter\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Entity\Field\FieldInterface;

/**
 * A payment form formatter.
 *
 * @FieldFormatter(
 *   id = "payment_form",
 *   label = @Translation("Payment form"),
 *   field_types = {
 *     "payment_form",
 *   }
 * )
 */
class PaymentForm extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $payment = entity_create('payment', array(
      'bundle' => 'payment_form',
    ))->setCurrencyCode($this->fieldDefinition->getFieldSetting('currency_code'));
    foreach ($items as $item) {
      $payment->setLineItem($item->line_item);
    }
    $payment->payment_form_field_instance = $this->fieldDefinition->id();

    return drupal_get_form(\Drupal::service('plugin.manager.entity')->getFormController('payment', 'payment_form')->setEntity($payment));
  }

}
