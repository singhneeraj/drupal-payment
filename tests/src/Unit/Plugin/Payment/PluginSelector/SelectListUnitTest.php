<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\SelectListUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector;

use Drupal\payment\Plugin\Payment\PluginSelector\SelectList;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\PluginSelector\SelectList
 *
 * @group Payment
 */
class SelectListUnitTest extends PluginSelectorBaseUnitTestBase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginSelector\SelectList
   */
  protected $sut;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new SelectList([], $this->pluginId, $this->pluginDefinition, $this->stringTranslation);
    $this->sut->setPluginManager($this->pluginManager, $this->mapper);
  }

  /**
   * @covers ::buildSelector
   * @covers ::buildHierarchy
   * @covers ::buildHierarchyLevel
   * @covers ::buildOptionsLevel
   * @covers ::sort
   */
  public function testBuildSelector() {
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $method = new \ReflectionMethod($this->sut, 'buildSelector');
    $method->setAccessible(TRUE);
    $get_element_id_method = new \ReflectionMethod($this->sut, 'getElementId');
    $get_element_id_method->setAccessible(TRUE);

    $plugin_id_a = $this->randomMachineName();
    $plugin_label_a = $this->randomMachineName();
    $plugin_definition_a = [
      'id' => $plugin_id_a,
      'label' => $plugin_label_a,
    ];
    $plugin_a = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin_a->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->willReturn($plugin_id_a);
    $plugin_id_b = $this->randomMachineName();
    $plugin_label_b = $this->randomMachineName();
    $plugin_definition_b = [
      'id' => $plugin_id_b,
      'label' => $plugin_label_b,
    ];
    $plugin_b = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');

    $map = [
      [$plugin_definition_a, $plugin_label_a],
      [$plugin_definition_b, $plugin_label_b],
    ];
    $this->mapper->expects($this->atLeastOnce())
      ->method('getPluginLabel')
      ->willReturnMap($map);

    $this->sut->setSelectedPlugin($plugin_a);
    $selector_title = $this->randomMachineName();
    $this->sut->setLabel($selector_title);

    $element = array(
      '#parents' => array('foo', 'bar'),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $available_plugins = [$plugin_a, $plugin_b];

    $this->pluginManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn([
        $plugin_id_a => $plugin_definition_a,
        $plugin_id_b => $plugin_definition_b,
      ]);

    $expected_build_plugin_id = array(
      '#ajax' => array(
        'callback' => array('Drupal\payment\Plugin\Payment\PluginSelector\SelectList', 'ajaxSubmitConfigurationForm'),
        'effect' => 'fade',
        'event' => 'change',
        'trigger_as' => array(
          'name' => 'foo[bar][select][container][change]',
        ),
        'wrapper' => $get_element_id_method->invokeArgs($this->sut, array($form_state)),
      ),
      '#default_value' => $plugin_id_a,
      '#empty_value' => 'select',
      '#options' => array(
        $plugin_id_a => $plugin_label_a,
        $plugin_id_b => $plugin_label_b,
      ) ,
      '#required' => FALSE,
      '#title' => $selector_title,
      '#type' => 'select',
    );
    $expected_build_change = array(
      '#ajax' => array(
        'callback' => array('Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase', 'ajaxRebuildForm'),
      ),
      '#attributes' => array(
        'class' => array('js-hide')
      ),
      '#limit_validation_errors' => array(array('foo', 'bar', 'select', 'plugin_id')),
      '#name' => 'foo[bar][select][container][change]',
      '#submit' => array(array($this->sut, 'rebuildForm')),
      '#type' => 'submit',
      '#value' => 'Choose',
    );
    $build = $method->invokeArgs($this->sut, array($element, $form_state, $available_plugins));
    $this->assertEquals($expected_build_plugin_id, $build['container']['plugin_id']);
    $this->assertEquals($expected_build_change, $build['container']['change']);
    $this->assertSame('container', $build['container']['#type']);
  }

}
