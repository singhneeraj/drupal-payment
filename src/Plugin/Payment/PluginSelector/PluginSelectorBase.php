<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorBase.
 */

namespace Drupal\payment\Plugin\Payment\PluginSelector;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\payment\Plugin\Payment\PluginDefinitionMapperInterface;

/**
 * Provides a base plugin selector.
 *
 * Plugins extending this class should provide a configuration schema that
 * extends payment.plugin_configuration.plugin_selector.payment_base.
 */
abstract class PluginSelectorBase extends PluginBase implements PluginSelectorInterface {

  /**
   * The mapper.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginDefinitionMapperInterface
   */
  protected $pluginDefinitionMapper;

  /**
   * The plugin manager of which to select plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * The previously selected plugins.
   *
   * @var \Drupal\Component\Plugin\PluginInspectionInterface[]
   */
  protected $previouslySelectedPlugins = [];

  /**
   * The selected plugin.
   *
   * @var \Drupal\Component\Plugin\PluginInspectionInterface
   */
  protected $selectedPlugin;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'label' => NULL,
      'required' => FALSE,
      'collect_plugin_configuration' => TRUE,
      'keep_previously_selected_plugins' => TRUE,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->configuration['label'] = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->configuration['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired($required = TRUE) {
    $this->configuration['required'] = $required;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired() {
    return $this->configuration['required'];
  }

  /**
   * {@inheritdoc}
   */
  public function setCollectPluginConfiguration($collect = TRUE) {
    $this->configuration['collect_plugin_configuration'] = $collect;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectPluginConfiguration() {
    return $this->configuration['collect_plugin_configuration'];
  }

  /**
   * {@inheritdoc}
   */
  public function setKeepPreviouslySelectedPlugins($keep = TRUE) {
    $this->configuration['keep_previously_selected_plugins'] = $keep;
    if ($keep === FALSE) {
      $this->setPreviouslySelectedPlugins([]);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeepPreviouslySelectedPlugins() {
    return $this->configuration['keep_previously_selected_plugins'];
  }

  /**
   * {@inheritdoc}
   */
  public function setPreviouslySelectedPlugins(array $plugins) {
    $this->previouslySelectedPlugins = $plugins;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviouslySelectedPlugins() {
    return $this->previouslySelectedPlugins;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectedPlugin() {
    return $this->selectedPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setSelectedPlugin(PluginInspectionInterface $plugin) {
    $this->selectedPlugin = $plugin;
    if ($this->getKeepPreviouslySelectedPlugins()) {
      $this->previouslySelectedPlugins[$plugin->getPluginId()] = $plugin;
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginManager(PluginManagerInterface $plugin_manager, PluginDefinitionMapperInterface $mapper) {
    $this->pluginDefinitionMapper = $mapper;
    $this->pluginManager = $plugin_manager;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildSelectorForm(array $form, FormStateInterface $form_state) {
    if (!$this->pluginManager || !$this->pluginDefinitionMapper) {
      throw new \RuntimeException('A plugin manager and mapper must be set through static::setPluginManager() first.');
    }

    return [];
  }

}
