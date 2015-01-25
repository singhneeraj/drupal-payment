<?php

/**
 * @file
 * Contains \Drupal\payment_test\Plugin\PluginTest\MockManager.
 */

namespace Drupal\payment_test\Plugin\PluginTest;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\StaticDiscovery;

/**
 * Provides a plugin manager for testing plugni-related functionality.
 */
class MockManager extends PluginManagerBase {

  /**
   * Constructs a new instance.
   */
  public function __construct() {
    $this->discovery = new StaticDiscovery();

    $this->discovery->setDefinition('payment_test_plugin', array(
      'label' => t('Plugin'),
      'class' => 'Drupal\payment_test\Plugin\PluginTest\MockPlugin',
    ));

    $this->discovery->setDefinition('payment_test_configurable_plugin', array(
      'label' => t('Configurable plugin'),
      'class' => 'Drupal\payment_test\Plugin\PluginTest\MockConfigurablePlugin',
    ));

    $this->factory = new DefaultFactory($this->discovery);
  }
}
