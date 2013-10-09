<?php

/**
 * @file
 * Definition of Drupal\payment_form\Plugin\field\widget\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\field\widget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldItemListInterface;
use Drupal\field\Annotation\FieldWidget;
use Drupal\field\Plugin\Type\Widget\WidgetBase;
use Drupal\payment\Element\PaymentLineItemsInput;

/**
 * A payment configuration widget.
 *
 * @FieldWidget(
 *   field_types = {
 *     "payment_form"
 *   },
 *   id = "payment_form",
 *   label = @Translation("Line item configuration"),
 *   multiple_values = "true"
 * )
 */
class PaymentForm extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $count = count($this->getSetting('line_items'));
    $summary = array(format_plural($count, t('!count line item.'), t('!count line items'), array(
      '!count' => $count,
    )));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $element['#items'] = $items;
    $element['#process'][] = array($this, 'formElementProcess');

    return $element;
  }

  /**
   * Implements form API #process callback.
   */
  public function formElementProcess(array $element, array &$form_state, array $form) {
    $element['array_parents'] = array(
      '#value' => $element['#array_parents'],
      '#type' => 'value',
    );
    $line_items = array();
    foreach ($element['#items'] as $item) {
      $line_items[] = $item->line_item;
    }
    $line_items = array_filter($line_items);
    $element['line_items'] = array(
      '#cardinality' => $this->fieldDefinition->getFieldCardinality(),
      '#default_value' => $line_items,
      '#type' => 'payment_line_items_input',
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, array &$form_state) {
    $element = NestedArray::getValue($form, array_merge(array_slice($values['array_parents'], count($form['#array_parents'])), array('line_items')));

    return PaymentLineItemsInput::getLineItems($element, $form_state);
  }

}
