<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference.
 */

namespace Drupal\payment_reference\Plugin\Field\FieldType;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\currency\Entity\Currency;
use Drupal\entity_reference\ConfigurableEntityReferenceItem;
use Drupal\payment\Element\PaymentLineItemsInput;
use Drupal\payment\Payment;

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
 *   label = @Translation("Payment reference"),
 *   list_class = "\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList"
 * )
 */
class PaymentReference extends ConfigurableEntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + array(
      'target_type' => 'payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultInstanceSettings() {
    return parent::defaultInstanceSettings() + array(
      'currency_code' => '',
      'line_items_data' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_storage_definition) {
    return array(
      'columns' => array(
        'target_id' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'target_id' => array('target_id'),
      ),
      'foreign keys' => array(
        'target_id' => array(
          'table' => 'payment',
          'columns' => array(
            'target_id' => 'id',
          ),
        ),
      ),
    );
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
      '#default_value' => $this->getSetting('currency_code'),
      '#required' => TRUE,
    );
    $line_items = array();
    foreach ($this->getSetting('line_items_data') as $line_item_data) {
      $line_items[] = Payment::lineItemManager()->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']);
    }
    $form['line_items'] = array(
      '#type' => 'payment_line_items_input',
      '#title' => $this->t('Line items'),
      '#default_value' => $line_items,
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
      $line_items_data = array();
      foreach (PaymentLineItemsInput::getLineItems($element['line_items'], $form_state) as $line_item) {
        $line_items_data[] = array(
          'plugin_id' => $line_item->getPluginId(),
          'plugin_configuration' => $line_item->getConfiguration(),
        );
      }
      $value = array(
        'currency_code' => $values['currency_code'],
        'line_items_data' => $line_items_data,
      );
      form_set_value($element, $value, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array &$form, array &$form_state, $has_data) {
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

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $payment_id = $this->get('target_id')->getValue();
    $queue = $this->getPaymentQueue();
    $acquisition_code = $queue->claimPayment($payment_id);
    if ($acquisition_code !== FALSE) {
      $queue->acquirePayment($payment_id, $acquisition_code);
    }
    else {
      $this->get('target_id')->setValue(0);
    }
  }

  /**
   * Gets the payment queue.
   *
   * @todo Inject this once https://drupal.org/node/2053415 is fixed.
   */
  protected function getPaymentQueue() {
    return \Drupal::service('payment_reference.queue');
  }
}
