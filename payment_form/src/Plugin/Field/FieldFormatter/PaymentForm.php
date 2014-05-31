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
    $line_items_data = array();
    foreach ($items as $item) {
      /** @var \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm $item */
      $plugin_id = $item->get('plugin_id')->getValue();
      if ($plugin_id) {
        $line_items_data[] = array(
          'plugin_id' => $plugin_id,
          'plugin_configuration' => $item->get('plugin_configuration')->getValue(),
        );
      }
    }

    $callback = __CLASS__ . '::viewElementsPostRenderCache';
    $context = array(
      'currency_code' => $this->fieldDefinition->getSetting('currency_code'),
      'field_definition_name' => $this->fieldDefinition->getName(),
      'line_items_data' => serialize($line_items_data),
    );
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context);

    return array(array(
      '#type' => 'markup',
      '#post_render_cache' => array(
        $callback => array($context),
      ),
      '#markup' => $placeholder,
    ));
  }

  /**
   * Implements #post_render_cache.
   */
  public static function viewElementsPostRenderCache(array $element, array $context) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = \Drupal::entityManager()->getStorage('payment')->create(array(
      'bundle' => 'payment_form',
    ));
    $payment->setCurrencyCode($context['currency_code']);
    /** @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setDestinationUrl(\Drupal::request()->getUri());
    $line_items_data = unserialize($context['line_items_data']);
    foreach ($line_items_data as $line_item_data) {
      $payment->setLineItem(Payment::lineItemManager()->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']));
    }
    $payment_type->setFieldInstanceConfigId($context['field_definition_name']);

    /** @var \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder */
    $entity_form_builder = \Drupal::service('entity.form_builder');
    $placeholder = drupal_render_cache_generate_placeholder(__METHOD__, $context, $context['token']);

    $build = $entity_form_builder->getForm($payment, 'payment_form');
    $element['#markup'] = str_replace($placeholder, drupal_render($build), $element['#markup']);

    return $element;
  }
}
