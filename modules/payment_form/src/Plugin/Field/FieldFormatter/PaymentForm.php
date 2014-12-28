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
        $line_items_data[] = array(
          'plugin_id' => $plugin_id,
          'plugin_configuration' => $item->get('plugin_configuration')->getValue(),
        );
      }
    }

    $callback = __CLASS__ . '::viewElementsPostRenderCache';
    $context = array(
      'bundle' => $items->getEntity()->bundle(),
      'entity_type_id' => $items->getEntity()->getEntityTypeId(),
      'field_name' => $this->fieldDefinition->getName(),
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
    $field_definitions = \Drupal::entityManager()->getFieldDefinitions($context['entity_type_id'], $context['bundle']);
    $field_definition = $field_definitions[$context['field_name']];
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = \Drupal::entityManager()->getStorage('payment')->create(array(
      'bundle' => 'payment_form',
    ));
    $payment->setCurrencyCode($field_definition->getSetting('currency_code'));
    /** @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setDestinationUrl(\Drupal::request()->getUri());
    $payment_type->setEntityTypeId($context['entity_type_id']);
    $payment_type->setBundle($context['bundle']);
    $payment_type->setFieldName($context['field_name']);
    $line_items_data = unserialize($context['line_items_data']);
    foreach ($line_items_data as $line_item_data) {
      $payment->setLineItem(Payment::lineItemManager()->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']));
    }

    /** @var \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder */
    $entity_form_builder = \Drupal::service('entity.form_builder');
    $placeholder = drupal_render_cache_generate_placeholder(__METHOD__, $context, $context['token']);

    $build = $entity_form_builder->getForm($payment, 'payment_form');
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $element['#markup'] = str_replace($placeholder, $renderer->render($build), $element['#markup']);

    return $element;
  }
}
