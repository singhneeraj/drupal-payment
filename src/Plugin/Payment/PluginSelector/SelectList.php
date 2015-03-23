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
      '#options' => $this->buildOptionsLevel($this->buildHierarchy()),
      '#required' => $this->isRequired(),
      '#title' => $this->getLabel(),
      '#type' => 'select',
    );

    return $element;
  }

  /**
   * Returns a hierarchical plugin representation.
   *
   * @return array[]
   *   A possibly infinitely nested associative array. Keys are plugin IDs and
   *   values are arrays of similar structure as this method's return value.
   */
  protected function buildHierarchy() {
    $parents = [];
    $children = [];
    $definitions = $this->pluginManager->getDefinitions();
    uasort($definitions, array($this, 'sort'));
    foreach ($definitions as $plugin_id => $definition) {
      $parent_plugin_id = $this->pluginDefinitionMapper->getParentPluginId($definition);
      if ($parent_plugin_id) {
        $children[$parent_plugin_id][] = $plugin_id;
      }
      else {
        $parents[] = $plugin_id;
      }
    }

    return $this->buildHierarchyLevel($parents, $children);
  }

  /**
   * Helper function for self::hierarchy().
   *
   * @param array $parent_plugin_ids
   *   An array with IDs of plugins that are part of the same hierarchy level.
   * @param array $child_plugin_ids
   *   Keys are plugin IDs. Values are arrays with those plugin's child
   *   plugin IDs.
   *
   * @return array[]
   *   The return value is identical to that of self::hierarchy().
   */
  protected function buildHierarchyLevel(array $parent_plugin_ids, array $child_plugin_ids) {
    $hierarchy = [];
    foreach ($parent_plugin_ids as $plugin_id) {
      $hierarchy[$plugin_id] = isset($child_plugin_ids[$plugin_id]) ? $this->buildHierarchyLevel($child_plugin_ids[$plugin_id], $child_plugin_ids) : [];
    }

    return $hierarchy;
  }

  /**
   * Helper function for self::options().
   *
   * @param array $hierarchy
   *   A plugin ID hierarchy as returned by self::hierarchy().
   * @param integer $depth
   *   The depth of $hierarchy's top-level items as seen from the original
   *   hierarchy's root (this function is recursive), starting with 0.
   *
   * @return string[]
   *   Keys are plugin IDs.
   */
  protected function buildOptionsLevel(array $hierarchy, $depth = 0) {
    $definitions = $this->pluginManager->getDefinitions();
    $options = [];
    $prefix = $depth ? str_repeat('-', $depth) . ' ' : '';
    foreach ($hierarchy as $plugin_id => $child_plugin_ids) {
      $options[$plugin_id] = $prefix . $this->pluginDefinitionMapper->getPluginLabel($definitions[$plugin_id]);
      $options += $this->buildOptionsLevel($child_plugin_ids, $depth + 1);
    }

    return $options;
  }

  /**
   * Implements uasort() callback to sort plugin definitions by label.
   */
  protected function sort(array $definition_a, array $definition_b) {
    return strcmp($this->pluginDefinitionMapper->getPluginLabel($definition_a), $this->pluginDefinitionMapper->getPluginLabel($definition_b));
  }

}
