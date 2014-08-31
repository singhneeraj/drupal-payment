<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\EntityExtraFieldInfo.
 */

namespace Drupal\payment\Hook;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Implements hook_entity_extra_field_info().
 *
 * @see payment_entity_extra_field_info()
 */
class EntityExtraFieldInfo {

  use StringTranslationTrait;

  /**
   * The payment type manager
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $paymentTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Component\Plugin\PluginManagerInterface $payment_type_manager
   */
  public function __construct(TranslationInterface $string_translation, PluginManagerInterface $payment_type_manager) {
    $this->paymentTypeManager = $payment_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    $fields = array();
    foreach (array_keys($this->paymentTypeManager->getDefinitions()) as $plugin_id) {
      $fields['payment'][$plugin_id] = array(
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
        ),
      );
    }
  
    return $fields;
  }

}
