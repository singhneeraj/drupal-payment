<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\FieldExtraFields.
 */

namespace Drupal\payment\Hook;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Implements hook_field_extra_fields().
 *
 * @see payment_field_extra_fields()
 */
class FieldExtraFields {

  /**
   * The payment type manager
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $paymentTypeManager;

  /**
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translationManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   * @param \Drupal\Component\Plugin\PluginManagerInterface $payment_type_manager
   */
  public function __construct(TranslationInterface $translation_manager, PluginManagerInterface $payment_type_manager) {
    $this->paymentTypeManager = $payment_type_manager;
    $this->translationManager = $translation_manager;
  }

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    $fields = array();
    foreach (array_keys($this->paymentTypeManager->getDefinitions()) as $plugin_id) {
      $fields['payment'][$plugin_id] = array(
        'form' => array(
          'payment_line_items' => array(
            'label' => $this->t('Line items'),
            'weight' => 0,
          ),
          'payment_method' => array(
            'label' => $this->t('Payment method selection and configuration'),
            'weight' => 0,
          ),
          'payment_status' => array(
            'label' => $this->t('Status'),
            'weight' => 0,
          ),
        ),
        'display' => array(
          'method' => array(
            'label' => $this->t('Payment method label'),
            'weight' => 0,
          ),
          'line_items' => array(
            'label' => $this->t('Line items'),
            'weight' => 0,
          ),
          'statuses' => array(
            'label' => $this->t('Status items'),
            'weight' => 0,
          ),
          'links' => array(
            'label' => $this->t('Links'),
            'weight' => 0,
          ),
        ),
      );
    }
  
    return $fields;
  }

  /**
   * Translates a string to the current language or to a given language.
   *
   * See the t() documentation for details.
   */
  protected function t($string, array $args = array(), array $options = array()) {
    return $this->translationManager->translate($string, $args, $options);
  }

}
