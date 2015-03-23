<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase.
 */

namespace Drupal\payment\Plugin\Payment\PluginSelector;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default base for most plugin selectors.
 *
 * This class takes care of everything, except the actual selection element.
 */
abstract class AdvancedPluginSelectorBase extends PluginSelectorBase implements ContainerFactoryPluginInterface {

  /**
   * The form element ID.
   *
   * @see self::getElementId
   *
   * @var string
   */
  protected $elementId;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildSelectorForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildSelectorForm($form, $form_state);

    $available_plugins = [];
    foreach ($this->pluginManager->getDefinitions() as $plugin_definition) {
      $plugin_id = $this->pluginDefinitionMapper->getPluginId($plugin_definition);
      $available_plugins[] = $this->pluginManager->createInstance($plugin_id);
    }
    if (count($available_plugins) == 0) {
      $callback_method = 'buildNoAvailablePlugins';
    }
    elseif (count($available_plugins) == 1) {
      $callback_method = 'buildOneAvailablePlugin';
    }
    else {
      $callback_method = 'buildMultipleAvailablePlugins';
    }

    $form['container'] = array(
      '#attributes' => array(
        'class' => array('payment-plugin-selector-' . Html::getId($this->getPluginId())),
      ),
      '#available_plugins' => $available_plugins,
      '#process' => array(array($this, $callback_method)),
      '#tree' => TRUE,
      '#type' => 'container',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSelectorForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $plugin_id = NestedArray::getValue($values, array_merge($form['container']['#parents'], array('select', 'container', 'plugin_id')));
    $selected_plugin = $this->getSelectedPlugin();
    if (!$selected_plugin && $plugin_id || $selected_plugin && $plugin_id != $selected_plugin->getPluginId()) {
      // Keep track of all previously selected plugins so their configuration
      // does not get lost.
      if (isset($this->getPreviouslySelectedPlugins()[$plugin_id])) {
        $this->setSelectedPlugin($this->getPreviouslySelectedPlugins()[$plugin_id]);
      }
      else {
        $this->setSelectedPlugin($this->pluginManager->createInstance($plugin_id));
      }
      // If a (different) plugin was chosen and its form must be displayed,
      // rebuild the form.
      if ($this->getCollectPluginConfiguration() && $this->getSelectedPlugin() instanceof PluginFormInterface) {
        $form_state->setRebuild();
      }
    }
    // If no (different) plugin was chosen, delegate validation to the plugin.
    elseif ($this->getCollectPluginConfiguration() && $selected_plugin instanceof PluginFormInterface) {
      $selected_plugin->validateConfigurationForm($form['container']['plugin_form'], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitSelectorForm(array &$form, FormStateInterface $form_state) {
    $selectedPlugin = $this->getSelectedPlugin();
    if ($this->getCollectPluginConfiguration() && $selectedPlugin instanceof PluginFormInterface) {
      $selectedPlugin->submitConfigurationForm($form['container']['plugin_form'], $form_state);
    }
  }

  /**
   * Implements form API's #submit.
   */
  public function rebuildForm(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Implements form AJAX callback.
   */
  public static function ajaxRebuildForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $form_parents = array_slice($triggering_element['#array_parents'], 0, -3);
    $root_element = NestedArray::getValue($form, $form_parents);

    return $root_element['plugin_form'];
  }

  /**
   * Builds the plugin configuration form elements.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  protected function buildPluginForm(FormStateInterface $form_state) {
    $element = array(
      '#attributes' => array(
        'class' => array('payment-plugin-selector-' . Html::getId($this->getPluginId()) . '-payment-plugin-form'),
      ),
      '#id' => $this->getElementId(),
      '#type' => 'container',
    );
    $selectedPlugin = $this->getSelectedPlugin();
    if ($this->getCollectPluginConfiguration() && $selectedPlugin instanceof PluginFormInterface) {
      $element += $selectedPlugin->buildConfigurationForm([], $form_state);
    }

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for when there are no available plugins.
   */
  public function buildNoAvailablePlugins(array $element, FormStateInterface $form_state, array $form) {
    $element['select']['container'] = array(
      '#type' => 'container',
    );
    $element['select']['container']['plugin_id'] = array(
      '#type' => 'value',
      '#value' => NULL,
    );
    $element['select']['message'] = array(
      '#markup' => $this->t('There are no available options.'),
    );

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for one plugin.
   */
  public function buildOneAvailablePlugin(array $element, FormStateInterface $form_state, array $form) {
    $plugin = reset($element['#available_plugins']);

    // Use the only available plugin if no other was configured before, or the
    // configured plugin is not available.
    if (is_null($this->getSelectedPlugin()) || $this->getSelectedPlugin()->getPluginId() != $plugin->getPluginId()) {
      $this->setSelectedPlugin($plugin);
    }

    $element['select']['container'] = array(
      '#type' => 'container',
    );
    $element['select']['container']['plugin_id'] = array(
      '#type' => 'value',
      '#value' => $this->getSelectedPlugin()->getPluginId(),
    );
    $element['plugin_form'] = $this->buildPluginForm($form_state);

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for multiple plugins.
   */
  public function buildMultipleAvailablePlugins(array $element, FormStateInterface $form_state, array $form) {
    $plugins = $element['#available_plugins'];

    $element['select'] = $this->buildSelector($element, $form_state, $plugins);
    $element['plugin_form'] = $this->buildPluginForm($form_state);

    return $element;
  }

  /**
   * Builds the form elements for the actual plugin selector.
   *
   * @param array $root_element
   *   The plugin's root element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form's state.
   * @param \Drupal\Component\Plugin\PluginInspectionInterface[] $plugins
   *   The available plugins.
   *
   * @return array
   *   The selector's form elements.
   */
  protected function buildSelector(array $root_element, FormStateInterface $form_state, array $plugins) {
    $build['container'] = array(
      '#attributes' => array(
        'class' => array('payment-plugin-selector-' . Html::getId($this->getPluginId() . '-selector')),
      ),
      '#type' => 'container',
    );
    $build['container']['plugin_id'] = array(
      '#markup' => 'This element must be overridden to provide the plugin ID.',
    );
    $root_element_parents = $root_element['#parents'];
    $change_button_name = array_shift($root_element_parents) . ($root_element_parents ? '[' . implode('][', $root_element_parents) . ']' : NULL) . '[select][container][change]';
    $build['container']['change'] = array(
      '#ajax' => array(
        'callback' => array(get_class(), 'ajaxRebuildForm'),
      ),
      '#attributes' => array(
        'class' => array('js-hide')
      ),
      '#limit_validation_errors' => array(array_merge($root_element['#parents'], array('select', 'plugin_id'))),
      '#name' => $change_button_name,
      '#submit' => array(array($this, 'rebuildForm')),
      '#type' => 'submit',
      '#value' => $this->t('Choose'),
    );

    return $build;
  }

  /**
   * Retrieves the element's ID from the form's state.
   *
   * @return string
   */
  protected function getElementId() {
    if (!$this->elementId) {
      $this->elementId = Html::getUniqueId($this->getPluginId());
    }

    return $this->elementId;
  }

}
