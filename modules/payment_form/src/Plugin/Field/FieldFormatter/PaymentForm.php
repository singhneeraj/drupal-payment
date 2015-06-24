<?php

/**
 * @file
 * Contains \Drupal\payment_form\Plugin\field\formatter\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\payment\Payment;

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
    $line_items_data = [];
    foreach ($items as $item) {
      /** @var \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm $item */
      $plugin_id = $item->get('plugin_id')->getValue();
      if ($plugin_id) {
        $line_items_data[] = [
          'plugin_id' => $plugin_id,
          'plugin_configuration' => $item->get('plugin_configuration')->getValue(),
        ];
      }
    }

    $callback = __CLASS__ . '::lazyBuild';

    return [[
      '#lazy_builder' => [$callback, [
        $items->getEntity()->bundle(),
        $items->getEntity()->getEntityTypeId(),
        $this->fieldDefinition->getName(),
        serialize($line_items_data),
      ]],
    ]];
  }

  /**
   * Implements #post_render_cache.
   */
  public static function lazyBuild($bundle, $entity_type_id, $field_name, $line_items_data) {
    $field_definitions = \Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle);
    $field_definition = $field_definitions[$field_name];
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = \Drupal::entityManager()->getStorage('payment')->create([
      'bundle' => 'payment_form',
    ]);
    $payment->setCurrencyCode($field_definition->getSetting('currency_code'));
    /** @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setDestinationUrl(\Drupal::request()->getUri());
    $payment_type->setEntityTypeId($entity_type_id);
    $payment_type->setBundle($bundle);
    $payment_type->setFieldName($field_name);
    $line_items_data = unserialize($line_items_data);
    foreach ($line_items_data as $line_item_data) {
      $payment->setLineItem(Payment::lineItemManager()->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']));
    }

    /** @var \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder */
    $entity_form_builder = \Drupal::service('entity.form_builder');

    return $entity_form_builder->getForm($payment, 'payment_form');
  }
}
