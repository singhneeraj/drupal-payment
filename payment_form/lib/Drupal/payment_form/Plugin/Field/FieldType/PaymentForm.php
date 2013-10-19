<?php

/**
 * Contains Drupal\payment_form\Plugin\field\field_type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Field\FieldType;

use Drupal\Core\Field\ConfigFieldItemBase;
use Drupal\currency\Entity\Currency;
use Drupal\field\FieldInterface;

/**
 * Defines a payment form field.
 *
 * @FieldType(
 *   default_widget = "payment_form",
 *   default_formatter = "payment_form",
 *   id = "payment_form",
 *   instance_settings = {
 *     "currency_code" = null
 *   },
 *   label = @Translation("Payment form")
 * )
 */
class PaymentForm extends ConfigFieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see static::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    $form['currency_code'] = array(
      '#type' => 'select',
      '#title' => t('Payment currency'),
      '#options' => Currency::options(),
      '#default_value' => $this->getFieldSetting('currency_code'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldInterface $field) {
    $schema = array(
      'columns' => array(
        // Line items are plugin instances, and there may be more data than the
        // four required pieces that we know of and can store separately.
        'line_item' => array(
          'type' => 'blob',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions = parent::getPropertyDefinitions();
      static::$propertyDefinitions['line_item'] = array(
        'label' => t('Line item'),
        'required' => TRUE,
        'type' => 'any',
      );
    }

    return static::$propertyDefinitions;
  }
}
