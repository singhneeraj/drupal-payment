<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\DataType\PluginConfiguration.
 */

namespace Drupal\payment\Plugin\DataType;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\TypedData\TypedData;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;

/**
 * Provides a plugin configuration data type.
 *
 * @DataType(
 *   id = "payment_plugin_configuration",
 *   label = @Translation("Plugin configuration")
 * )
 */
class PluginConfiguration extends TypedData {

  /**
   * The plugin configuration.
   *
   * @var mixed[]
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    $value = (array) $value;
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $parent */
    $parent = $this->getParent();
    $plugin_instance = $parent->getContainedPluginInstance();
    if ($plugin_instance instanceof ConfigurablePluginInterface) {
      $plugin_instance->setConfiguration($value);
      $this->parent->onChange($this->getName());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $parent */
    $parent = $this->getParent();
    $plugin_instance = $parent->getContainedPluginInstance();
    if ($plugin_instance instanceof ConfigurablePluginInterface) {
      return $plugin_instance->getConfiguration();
    }
    return [];
  }

}
