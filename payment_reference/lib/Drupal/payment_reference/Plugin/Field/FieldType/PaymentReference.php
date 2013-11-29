<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference.
 */

namespace Drupal\payment_reference\Plugin\Field\FieldType;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\currency\Entity\Currency;
use Drupal\entity_reference\ConfigurableEntityReferenceItem;
use Drupal\payment\Element\PaymentLineItemsInput;

/**
 * Provides a configurable payment reference field.
 *
 * This field cannot be used as a base field.
 *
 * @FieldType(
 *   configurable = "true",
 *   constraints = {
 *     "ValidReference" = TRUE
 *   },
 *   default_formatter = "entity_reference_label",
 *   default_widget = "payment_reference",
 *   id = "payment_reference",
 *   instance_settings = {
 *     "currency_code" = "",
 *     "line_items_data" = {}
 *   },
 *   label = @Translation("Payment reference"),
 *   list_class = "\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList"
 * )
 */
class PaymentReference extends ConfigurableEntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    // Merge in fixed settings.
    $definition['settings'] = array(
      'target_bundle' => 'payment_reference',
      'target_type' => 'payment',
    );
    parent::__construct($definition, $name, $parent);
  }

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    $form['#element_validate'] = array(get_class() . '::instanceSettingsFormValidate');
    $form['currency_code'] = array(
      '#empty_value' => '',
      '#type' => 'select',
      '#title' => $this->t('Payment currency'),
      '#options' => $this->currencyOptions(),
      '#default_value' => $this->getFieldSetting('currency_code'),
      '#required' => TRUE,
    );
    $form['line_items'] = array(
      '#type' => 'payment_line_items_input',
      '#title' => $this->t('Line items'),
      '#default_value' => $this->getFieldSetting('line_items_data'),
      '#required' => TRUE,
      '#currency_code' => '',
    );

    return $form;
  }

  /**
   * Implements #element_validate callback for self::instanceSettingsForm().
   */
  public static function instanceSettingsFormValidate(array $element, array &$form_state) {
    $add_more_button_form_parents = array_merge($element['#array_parents'], array('line_items', 'add_more', 'add'));
    // Only set the field settings as a value when it is not the "Add more"
    // button that has been clicked.
    if ($form_state['triggering_element']['#array_parents'] != $add_more_button_form_parents) {
      $values = NestedArray::getValue($form_state['values'], $element['#array_parents']);
      $value = array(
        'currency_code' => $values['currency_code'],
        'line_items_data' => PaymentLineItemsInput::getLineItemsData($element['line_items'], $form_state),
      );
      form_set_value($element, $value, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state, $has_data) {
    return array();
  }

  /**
   * Wraps \Drupal\currency\Entity\Currency::options().
   *
   * @todo Revisit this when https://drupal.org/node/2118295 is fixed.
   */
  protected function currencyOptions() {
    return Currency::options();
  }

  /**
   * Wraps t().
   *
   * @todo Revisit this when we can use traits and use those to wrap the
   *   translation manager.
   */
  protected function t($string, array $args = array(), array $options = array()) {
    return t($string, $args, $options);
  }
}
