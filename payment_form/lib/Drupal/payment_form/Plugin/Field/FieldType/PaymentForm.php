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
      '#title' => $this->t('Payment currency'),
      '#options' => $this->currencyOptions(),
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
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions = parent::getPropertyDefinitions();
      static::$propertyDefinitions['plugin_configuration'] = array(
        'settings' => array(
          'default_value' => array(),
        ),
        'label' => $this->t('Plugin configuration'),
        'required' => TRUE,
        'type' => 'any',
      );
      static::$propertyDefinitions['plugin_id'] = array(
        'label' => $this->t('Plugin ID'),
        'required' => TRUE,
        'type' => 'string',
      );
    }

    return static::$propertyDefinitions;
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
   * Wraps \Drupal\currency\Entity\Currency::options().
   *
   * @todo Revisit this when https://drupal.org/node/2118295 is fixed.
   */
  protected function currencyOptions() {
    return Currency::options();
  }
}
