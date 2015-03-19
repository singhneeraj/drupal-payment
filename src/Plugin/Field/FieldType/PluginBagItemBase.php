<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PluginBagItemBase.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Provides a base for plugin bag field items.
 */
abstract class PluginBagItemBase extends FieldItemBase implements PluginBagItemInterface {

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    // @todo Remove this override once https://www.drupal.org/node/2413471 has
    //   been fixed.
    // There is either a property object or a plain value - possibly for a
    // not-defined property. If we have a plain value, directly return it.
    if ($this->definition->getPropertyDefinition($name)) {
      return $this->get($name)->getValue();
    }
    elseif (isset($this->values[$name])) {
      return $this->values[$name];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validatePluginInstance(PluginInspectionInterface $plugin_instance) {
    if (!$this->getPluginManager()->hasDefinition($plugin_instance->getPluginId())) {
      throw new \Exception(sprintf('Plugin manager %s does not have a definition for plugin %s.', get_class($this->getPluginManager()), $plugin_instance->getPluginId()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createContainedPluginInstance($plugin_id, array $plugin_configuration = []) {
    $plugin_instance = $this->getPluginManager()
      ->createInstance($plugin_id, $plugin_configuration);
    $this->validatePluginInstance($plugin_instance);

    return $plugin_instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getContainedPluginInstance() {
    return $this->get('plugin_instance')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setContainedPluginInstance(PluginInspectionInterface $plugin_instance) {
    $this->get('plugin_instance')->setValue($plugin_instance);

    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getContainedPluginId() {
    return $this->get('plugin_id')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setContainedPluginId($plugin_id) {
    $this->get('plugin_id')->setValue($plugin_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContainedPluginConfiguration() {
    return $this->get('plugin_configuration')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setContainedPluginConfiguration(array $configuration) {
    $this->get('plugin_configuration')->setValue($configuration);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['plugin_id'] = DataDefinition::create('payment_plugin_id')
      ->setLabel(t('Plugin ID'));
    $properties['plugin_configuration'] = MapDataDefinition::create('payment_plugin_configuration')
      ->setLabel(t('Plugin configuration'));
    $properties['plugin_instance'] = MapDataDefinition::create('payment_plugin_instance')
      ->setLabel(t('Plugin instance'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'plugin_instance';
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $columns = array(
      'plugin_id' => array(
        'description' => 'The plugin ID.',
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
      ),
      'plugin_configuration' => array(
        'description' => 'The plugin configuration.',
        'type' => 'blob',
        'not null' => TRUE,
        'serialize' => TRUE,
      ),
    );

    $schema = array(
      'columns' => $columns,
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return !$this->getContainedPluginInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if ($values instanceof PluginInspectionInterface) {
      $this->setContainedPluginInstance($values);
    }
    elseif (is_array($values)) {
      if (isset($values['plugin_instance'])) {
        $this->setContainedPluginInstance($values['plugin_instance']);
      }
      else {
        if (isset($values['plugin_id'])) {
          $this->setContainedPluginId($values['plugin_id']);
        }
        if (isset($values['plugin_configuration'])) {
          $this->setContainedPluginConfiguration($values['plugin_configuration']);
        }
      }
    }
    // Field API has this weird habit of setting NULL instead of calling
    // applyDefaultValue(), so we can't throw an exception on that.
    elseif (!is_null($values)) {
      $type = is_object($values) ? get_class($values) : gettype($values);
      throw new \InvalidArgumentException(sprintf('The value must implement \Drupal\Component\Plugin\PluginInspectionInterface or be an associative array, but %s was given', $type));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($name) {
    // All properties depend on the main property.
    return parent::__isset($this->mainPropertyName());
  }

}
