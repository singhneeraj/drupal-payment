<?php

/**
 * Contains \Drupal\payment_form\Plugin\field\field_type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\currency\Entity\Currency;

/**
 * Defines a payment form field.
 *
 * @FieldType(
 *   default_widget = "payment_form",
 *   default_formatter = "payment_form",
 *   id = "payment_form",
 *   label = @Translation("Payment form")
 * )
 */
class PaymentForm extends FieldItemBase {

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
  public static function defaultFieldSettings() {
    return [
      'currency_code' => 'XXX',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\currency\FormHelperInterface $form_helper */
    $form_helper = \Drupal::service('currency.form_helper');

    $element['currency_code'] = array(
      '#type' => 'select',
      '#title' => $this->t('Payment currency'),
      '#options' => $form_helper->getCurrencyOptions(),
      '#default_value' => $this->getSetting('currency_code'),
      '#required' => TRUE,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_storage_definition) {
    $schema = array(
      'columns' => array(
        'plugin_configuration' => array(
          'type' => 'blob',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'plugin_id' => array(
          'type' => 'varchar',
          'length' => 255,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_storage_definition) {
    // @todo Find out how to test this method, as it cannot use t() or
    //   self::t().
    $definitions = array();
    $definitions['plugin_configuration'] = DataDefinition::create('any')
      ->setLabel(t('Plugin configuration'))
      ->setRequired(TRUE);
    $definitions['plugin_id'] = DataDefinition::create('string')
      ->setLabel(t('Plugin ID'))
      ->setRequired(TRUE);

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->get('plugin_id')->setValue('', $notify);
    $this->get('plugin_configuration')->setValue(array(), $notify);

    return $this;
  }
}
