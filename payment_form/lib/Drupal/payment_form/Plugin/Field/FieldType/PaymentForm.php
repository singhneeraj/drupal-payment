<?php

/**
 * Contains \Drupal\payment_form\Plugin\field\field_type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\currency\Entity\Currency;

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
  public function instanceSettingsForm(array $form, array &$form_state) {
    $form['currency_code'] = array(
      '#type' => 'select',
      '#title' => $this->t('Payment currency'),
      '#options' => $this->currencyOptions(),
      '#default_value' => $this->getSetting('currency_code'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldDefinitionInterface $field_definition) {
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
      static::$propertyDefinitions['plugin_configuration'] = DataDefinition::create('any')
        ->setLabel($this->t('Plugin configuration'))
        ->setRequired(TRUE);
      static::$propertyDefinitions['plugin_id'] = DataDefinition::create('string')
        ->setLabel($this->t('Plugin ID'))
        ->setRequired(TRUE);
    }

    return static::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldDefinitionInterface $field_definition) {
    // @todo Find out the difference between this method and
    //   $this->getPropertyDefinitions().
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

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->get('plugin_id')->setValue('', $notify);
    $this->get('plugin_configuration')->setValue(array(), $notify);

    return $this;
  }
}
