<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment\Unit\Plugin\Field\FieldFormatter\PluginLabelUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Field\FieldFormatter;

use Drupal\payment\Plugin\Field\FieldFormatter\PluginLabel;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Field\FieldFormatter\PluginLabel
 *
 * @group Payment
 */
class PluginLabelUnitTest extends UnitTestCase {

  /**
   * The field definition used for testing.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldDefinition;

  /**
   * The field formatter under test.
   *
   * @var \Drupal\payment\Plugin\Field\FieldFormatter\PluginLabel
   */
  protected $fieldFormatter;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');

    $this->fieldFormatter = new PluginLabel('payment_plugin_label', [], $this->fieldDefinition, [], $this->randomMachineName(), $this->randomMachineName(), []);
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElements() {
    $plugin_label_a = $this->randomMachineName();
    $plugin_label_b = $this->randomMachineName();

    $plugin_definition_a = [
      'label' => $plugin_label_a,
    ];

    $plugin_definition_b = [
      'label' => $plugin_label_b,
    ];

    $plugin_instance_a = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin_instance_a->expects($this->atLeastOnce())
      ->method('getPluginDefinition')
      ->willReturn($plugin_definition_a);

    $plugin_instance_b = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin_instance_b->expects($this->atLeastOnce())
      ->method('getPluginDefinition')
      ->willReturn($plugin_definition_b);

    $item_a = $this->getMock('\Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface');
    $item_a->expects($this->atLeastOnce())
      ->method('getContainedPluginInstance')
      ->willReturn($plugin_instance_a);

    $item_b = $this->getMock('\Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface');
    $item_b->expects($this->atLeastOnce())
      ->method('getContainedPluginInstance')
      ->willReturn($plugin_instance_b);

    $iterator = new \ArrayIterator([$item_a, $item_b]);
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->setMethods(array('getEntity', 'getIterator'))
      ->getMock();
    $items->expects($this->atLeastOnce())
      ->method('getIterator')
      ->will($this->returnValue($iterator));

    $expected_build = [
      [
        '#markup' => $plugin_label_a,
      ],
      [
        '#markup' => $plugin_label_b,
      ],
    ];

    $this->assertSame($expected_build, $this->fieldFormatter->viewElements($items));
  }

}
