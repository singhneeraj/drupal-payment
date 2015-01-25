<?php

/**
 * @file
 * Contains \Drupal\payment_test\Plugin\Field\FieldType\PluginBagItem.
 */

namespace Drupal\payment_test\Plugin\Field\FieldType;

use Drupal\payment\Plugin\Field\FieldType\PluginBagItemBase;
use Drupal\payment_test\Plugin\PluginTest\MockManager;

/**
 * Provides a plugin bag field item for testing.
 *
 * @FieldType(
 *   id = "payment_test_plugin_bag",
 *   label = @Translation("Plugin bag")
 * )
 */
class MockPluginBagItem extends PluginBagItemBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginManager() {
    return new MockManager();
  }

}
