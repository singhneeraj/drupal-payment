<?php

/**
 * @file
 * Contains \Drupal\field\Tests\PluginBagItemBaseTest.
 */

namespace Drupal\payment\Tests\Plugin\Field\FieldType;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\payment_test\Plugin\PluginTest\MockConfigurablePlugin;
use Drupal\payment_test\Plugin\PluginTest\MockManager;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests \Drupal\payment\Plugin\Field\Plugin\Field\FieldType\PluginBagItemBase.
 *
 * @group Payment
 */
class PluginBagItemBaseTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['payment', 'payment_test', 'plugin_test'];

  /**
   * The field item under test.
   *
   * @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemBase
   */
  protected $fieldItem;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $field_definition = BaseFieldDefinition::create('payment_test_plugin_bag');

    $this->fieldItem = \Drupal::typedDataManager()->create($field_definition)[0];
  }

  /**
   * Tests the field.
   */
  protected function testField() {
    $plugin_id = 'payment_test_plugin';
    $plugin_id_configurable = 'payment_test_configurable_plugin';
    $plugin_configuration = [
      'foo' => $this->randomMachineName()
    ];

    // Test default values.
    $this->assertEqual($this->fieldItem->getContainedPluginId(), '');
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), []);
    $this->assertNull($this->fieldItem->getContainedPluginInstance());

    // Test setting values and auto-instantiation for a non-configurable plugin.
    $this->fieldItem->setContainedPluginId($plugin_id);
    $this->assertEqual($this->fieldItem->getContainedPluginId(), $plugin_id);
    $this->fieldItem->setContainedPluginConfiguration($plugin_configuration);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), []);
    $this->assertEqual($this->fieldItem->getContainedPluginInstance()->getPluginId(), $plugin_id);

    // Test setting values and auto-instantiation for a configurable plugin.
    $this->fieldItem->setContainedPluginId($plugin_id_configurable);
    $this->assertEqual($this->fieldItem->getContainedPluginId(), $plugin_id_configurable);
    $this->fieldItem->setContainedPluginConfiguration($plugin_configuration);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $plugin_configuration);
    $this->assertEqual($this->fieldItem->getContainedPluginInstance()->getPluginId(), $plugin_id_configurable);
    /** @var \Drupal\payment_test\Plugin\PluginTest\MockConfigurablePlugin $plugin_instance_a */
    $plugin_instance_a = $this->fieldItem->getContainedPluginInstance();
    $this->assertTrue($plugin_instance_a instanceof MockConfigurablePlugin);
    $this->assertEqual($plugin_instance_a->getConfiguration(), $plugin_configuration);
    $altered_plugin_configuration = $plugin_configuration += [
      'bar' => $this->randomMachineName(),
    ];
    $plugin_instance_a->setConfiguration($altered_plugin_configuration);
    $this->assertEqual($plugin_instance_a->getConfiguration(), $altered_plugin_configuration);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $altered_plugin_configuration);

    // Test resetting the values.
    $this->fieldItem->applyDefaultValue();
    $this->assertEqual($this->fieldItem->getContainedPluginId(), '');
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), []);
    $this->assertNull($this->fieldItem->getContainedPluginInstance());

    // Test setting values again and auto-instantiation.
    $this->fieldItem->applyDefaultValue();
    $this->fieldItem->setContainedPluginId($plugin_id_configurable);
    $this->assertEqual($this->fieldItem->getContainedPluginId(), $plugin_id_configurable);
    $this->fieldItem->setContainedPluginConfiguration($plugin_configuration);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $plugin_configuration);
    /** @var \Drupal\payment_test\Plugin\PluginTest\MockConfigurablePlugin $plugin_instance_b */
    $plugin_instance_b = $this->fieldItem->getContainedPluginInstance();
    $this->assertTrue($plugin_instance_b instanceof MockConfigurablePlugin);
    $this->assertEqual($plugin_instance_b->getConfiguration(), $plugin_configuration);
    // Make sure this is indeed a new instance and not the old one.
    $this->assertNotIdentical(spl_object_hash($plugin_instance_b), spl_object_hash($plugin_instance_a));
    // Make sure changing the configuration on the new instance changes the
    // configuration in the field item.
    $altered_plugin_configuration_a = $plugin_configuration + [
      'bar' => $this->randomMachineName(),
    ];
    $altered_plugin_configuration_b = $plugin_configuration + [
      'baz' => $this->randomMachineName(),
    ];
    $plugin_instance_b->setConfiguration($altered_plugin_configuration_b);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $altered_plugin_configuration_b);
    // Make sure changing the configuration on the old instance no longer has
    // any effect on the field item.
    $plugin_instance_a->setConfiguration($altered_plugin_configuration_a);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $altered_plugin_configuration_b);

    // Test feedback from the plugin back to the field item.
    $plugin_manager = new MockManager();
    /** @var \Drupal\payment_test\Plugin\PluginTest\MockConfigurablePlugin $plugin_instance_c */
    $plugin_configuration_c = $plugin_configuration + [
        'qux' => $this->randomMachineName(),
      ];
    $plugin_instance_c = $plugin_manager->createInstance($plugin_id_configurable, $plugin_configuration_c);
    $this->fieldItem->setContainedPluginInstance($plugin_instance_c);
    $this->assertEqual(spl_object_hash($this->fieldItem->getContainedPluginInstance()), spl_object_hash($plugin_instance_c));
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $plugin_configuration_c);
    $altered_plugin_configuration_c = $plugin_configuration_c + [
        'foobar' => $this->randomMachineName(),
      ];
    $plugin_instance_c->setConfiguration($altered_plugin_configuration_c);
    $this->assertEqual($this->fieldItem->getContainedPluginConfiguration(), $altered_plugin_configuration_c);

    // Test setting the main property.
    /** @var \Drupal\payment_test\Plugin\PluginTest\MockConfigurablePlugin $plugin_instance_d */
    $plugin_instance_d = $plugin_manager->createInstance($plugin_id_configurable);
    $plugin_instance_d->setConfiguration([
      'oman' => '42',
    ]);
    $this->fieldItem->setValue($plugin_instance_d);
    $this->assertEqual(spl_object_hash($this->fieldItem->getContainedPluginInstance()), spl_object_hash($plugin_instance_d));
  }

}
