<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\PluginSelector\SelectList.
 */

namespace Drupal\payment\Plugin\Payment\PluginSelector;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a plugin selector using a <select> element.
 *
 * @PluginSelector(
 *   id = "payment_select_list",
 *   label = @Translation("Drop-down selection list")
 * )
 */
class SelectList extends AdvancedPluginSelectorBase {

  /**
   * {@inheritdoc}
   */
  protected function buildSelector(array $root_element, FormStateInterface $form_state, array $plugins) {
    $element = parent::buildSelector($root_element, $form_state, $plugins);
    /** @var \Drupal\Component\Plugin\PluginInspectionInterface[] $plugins */
    $plugin_options = [];
    foreach ($plugins as $plugin) {
      $plugin_options[$plugin->getPluginId()] = $this->pluginDefinitionMapper->getPluginLabel($plugin->getPluginDefinition());
    }
    natcasesort($plugin_options);
    $element['container']['plugin_id'] = array(
      '#ajax' => array(
        'callback' => array(get_class(), 'ajaxSubmitConfigurationForm'),
        'effect' => 'fade',
        'event' => 'change',
        'trigger_as' => array(
          'name' => $element['container']['change']['#name'],
        ),
        'wrapper' => $this->getElementId(),
      ),
      '#default_value' => $this->getSelectedPlugin() ? $this->getSelectedPlugin()->getPluginId() : NULL,
      '#empty_value' => 'select',
      '#options' => $plugin_options ,
      '#required' => $this->isRequired(),
      '#title' => $this->getLabel(),
      '#type' => 'select',
    );

    return $element;
  }

}
