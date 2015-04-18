<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\PluginFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase
 *
 * @group Payment
 */
class AdvancedPluginSelectorBaseUnitTest extends PluginSelectorBaseUnitTestBase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $sut;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The response policy.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicyInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $responsePolicy;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->responsePolicy = $this->getMockBuilder('\Drupal\Core\PageCache\ResponsePolicy\KillSwitch')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase')
      ->setConstructorArgs(array([], $this->pluginId, $this->pluginDefinition, $this->stringTranslation, $this->responsePolicy))
      ->getMockForAbstractClass();
    $this->sut->setPluginManager($this->pluginManager, $this->mapper);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      ['page_cache_kill_switch', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->responsePolicy],
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase $class */
    $class = get_class($this->sut);
    $plugin = $class::create($container, [], $this->pluginId, $this->pluginDefinition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase', $plugin);
  }

  /**
   * @covers ::buildPluginForm
   */
  public function testBuildPluginForm() {
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $plugin_form = array(
      '#foo' => $this->randomMachineName(),
    );

    $plugin = $this->getMockForAbstractClass('\Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBaseUnitTestPluginFormPlugin');
    $plugin->expects($this->once())
      ->method('buildConfigurationForm')
      ->with([], $form_state)
      ->will($this->returnValue($plugin_form));


    $method = new \ReflectionMethod($this->sut, 'buildPluginForm');
    $method->setAccessible(TRUE);

    $build = $method->invoke($this->sut, $form_state);
    $this->assertSame('container', $build['#type']);

    $this->sut->setSelectedPlugin($plugin);
    $build = $method->invoke($this->sut, $form_state);
    $this->assertSame('container', $build['#type']);
    $this->assertSame($plugin_form['#foo'], $build['#foo']);
  }

  /**
   * @covers ::buildPluginForm
   */
  public function testBuildPluginFormWithoutPluginForm() {
    $form_state = new FormState();

    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin->expects($this->never())
      ->method('buildConfigurationForm');

    $method = new \ReflectionMethod($this->sut, 'buildPluginForm');
    $method->setAccessible(TRUE);

    $build = $method->invoke($this->sut, $form_state);
    $this->assertSame('container', $build['#type']);

    $this->sut->setSelectedPlugin($plugin);
    $build = $method->invoke($this->sut, $form_state);
    $this->assertSame('container', $build['#type']);
  }

  /**
   * @covers ::buildSelectorForm
   */
  public function testBuildSelectorFormWithoutAvailablePlugins() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $this->pluginManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue([]));

    $build = $this->sut->buildSelectorForm($form, $form_state);

    $expected_build = array(
      'container' => array(
        '#attributes' => array(
          'class' => array('payment-plugin-selector-' . Html::getId($this->pluginId)),
        ),
        '#available_plugins' => [],
        '#process' => array(array($this->sut, 'buildNoAvailablePlugins')),
        '#tree' => TRUE,
        '#type' => 'container',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::buildSelectorForm
   */
  public function testBuildSelectorFormWithOneAvailablePlugin() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $plugin_id = $this->randomMachineName();
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');

    $plugin_definitions = [
      [
        'id' => $plugin_id,
      ],
    ];

    $this->mapper->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->with($plugin_definitions[0])
      ->willReturn($plugin_definitions[0]['id']);

    $this->pluginManager->expects($this->any())
      ->method('createInstance')
      ->with($plugin_id)
      ->willReturn($plugin);
    $this->pluginManager->expects($this->any())
      ->method('getDefinitions')
      ->willReturn($plugin_definitions);

    $build = $this->sut->buildSelectorForm($form, $form_state);

    $expected_build = array(
      'container' => array(
        '#attributes' => array(
          'class' => array('payment-plugin-selector-' . Html::getId($this->pluginId)),
        ),
        '#available_plugins' => [$plugin],
        '#process' => array(array($this->sut, 'buildOneAvailablePlugin')),
        '#tree' => TRUE,
        '#type' => 'container',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::buildSelectorForm
   */
  public function testBuildSelectorFormWithMultipleAvailablePlugins() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $plugin_id_a = $this->randomMachineName();
    $plugin_a = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin_id_b = $this->randomMachineName();
    $plugin_b = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');

    $plugin_definitions = [
      [
        'id' => $plugin_id_a,
      ],
      [
        'id' => $plugin_id_b,
      ],
    ];

    $map = [
      [$plugin_definitions[0], $plugin_definitions[0]['id']],
      [$plugin_definitions[1], $plugin_definitions[1]['id']],
    ];
    $this->mapper->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->willReturnMap($map);

    $map = [
      [$plugin_id_a, [], $plugin_a],
      [$plugin_id_b, [], $plugin_b],
    ];
    $this->pluginManager->expects($this->any())
      ->method('createInstance')
      ->willReturnMap($map);
    $this->pluginManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue($plugin_definitions));

    $build = $this->sut->buildSelectorForm($form, $form_state);

    $expected_build = array(
      'container' => array(
        '#attributes' => array(
          'class' => array('payment-plugin-selector-' . Html::getId($this->pluginId)),
        ),
        '#available_plugins' => array($plugin_a, $plugin_b),
        '#process' => array(array($this->sut, 'buildMultipleAvailablePlugins')),
        '#tree' => TRUE,
        '#type' => 'container',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::submitSelectorForm
   */
  public function testSubmitSelectorForm() {
    $form = array(
      'container' => array(
        'plugin_form' => array(
          $this->randomMachineName() => [],
        ),
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $plugin = $this->getMockForAbstractClass('\Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBaseUnitTestPluginFormPlugin');
    $plugin->expects($this->once())
      ->method('submitConfigurationForm')
      ->with($form['container']['plugin_form'], $form_state);

    $this->sut->submitSelectorForm($form, $form_state);
    $this->sut->setSelectedPlugin($plugin);
    $this->sut->submitSelectorForm($form, $form_state);
  }

  /**
   * @covers ::validateSelectorForm
   */
  public function testValidateSelectorForm() {
    $plugin_id_a = $this->randomMachineName();
    $plugin_id_b = $this->randomMachineName();

    $form = array(
      'container' => array(
        '#parents' => array('foo', 'bar', 'container'),
        'plugin_form' => array(
          $this->randomMachineName() => [],
        ),
      ),
    );

    $plugin_a = $this->getMockForAbstractClass('\Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBaseUnitTestPluginFormPlugin');
    $plugin_a->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id_a));
    $plugin_b = $this->getMockForAbstractClass('\Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBaseUnitTestPluginFormPlugin');
    $plugin_b->expects($this->never())
      ->method('validateConfigurationForm');
    $plugin_b->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id_b));

    $map = array(
      array($plugin_id_a, [], $plugin_a),
      array($plugin_id_b, [], $plugin_b),
    );
    $this->pluginManager->expects($this->exactly(2))
      ->method('createInstance')
      ->will($this->returnValueMap($map));

    // The plugin is set for the first time. The plugin form must not be
    // validated, as there is no input for it yet.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'plugin_id' => $plugin_id_a,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->once())
      ->method('setRebuild');
    $this->sut->validateSelectorForm($form, $form_state);
    $this->assertSame($plugin_a, $this->sut->getSelectedPlugin());

    // The form is validated, but the plugin remains unchanged, and as such
    // should validate its own form as well.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'plugin_id' => $plugin_id_a,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->never())
      ->method('setRebuild');
    $plugin_a->expects($this->once())
      ->method('validateConfigurationForm')
      ->with($form['container']['plugin_form'], $form_state);
    $this->sut->validateSelectorForm($form, $form_state);
    $this->assertSame($plugin_a, $this->sut->getSelectedPlugin());

    // The plugin has changed. The plugin form must not be validated, as there
    // is no input for it yet.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'plugin_id' => $plugin_id_b,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->once())
      ->method('setRebuild');
    $this->sut->validateSelectorForm($form, $form_state);
    $this->assertSame($plugin_b, $this->sut->getSelectedPlugin());

    // Change the plugin ID back to the original. No new plugin may be
    // instantiated, nor must the plugin form be validated.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'plugin_id' => $plugin_id_a,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->once())
      ->method('setRebuild');
    $this->sut->validateSelectorForm($form, $form_state);
    $this->assertSame($plugin_a, $this->sut->getSelectedPlugin());
  }

  /**
   * @covers ::ajaxRebuildForm
   */
  public function testAjaxRebuildForm() {
    $form = array(
      'foo' => array(
        'bar' => array(
          'container' => array(
            'select' => array(
              'container' => array(
                'change' => array(
                  '#array_parents' => array('foo', 'bar', 'container', 'select', 'container', 'change'),
                ),
              ),
            ),
            'plugin_form' => array(
              $this->randomMachineName() => [],
            ),
          ),
        ),
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getTriggeringElement')
      ->willReturn($form['foo']['bar']['container']['select']['container']['change']);

    $build = $this->sut->ajaxRebuildForm($form, $form_state);
    $this->assertSame($form['foo']['bar']['container']['plugin_form'], $build);
  }

  /**
   * @covers ::getElementId
   */
  public function testGetElementId() {
    $method = new \ReflectionMethod($this->sut, 'getElementId');
    $method->setAccessible(TRUE);
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $element_id = $method->invokeArgs($this->sut, array($form_state));
    $this->assertInternalType('integer', strlen($element_id));
    $this->assertSame($element_id, $method->invokeArgs($this->sut, array($form_state)));
  }

  /**
   * @covers ::rebuildForm
   */
  public function testRebuildForm() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('setRebuild')
      ->with(TRUE);

    $this->sut->rebuildForm($form, $form_state);
  }

  /**
   * @covers ::buildNoAvailablePlugins
   */
  public function testBuildNoAvailablePlugins() {
    $element = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = [];

    $label = $this->randomMachineName();

    $this->sut->setLabel($label);

    $expected_build = $element + array(
      'select' => array(
        'message' => array(
          '#markup' => 'There are no available options.',
          '#title' => $label,
          '#type' => 'item',
        ),
        'container' => array(
          '#type' => 'container',
          'plugin_id' => array(
            '#type' => 'value',
            '#value' => NULL,
          ),
        ),
      ),
    );
    $this->assertEquals($expected_build, $this->sut->buildNoAvailablePlugins($element, $form_state, $form));
  }

  /**
   * @covers ::buildOneAvailablePlugin
   */
  public function testBuildOneAvailablePlugin() {
    $plugin_id = $this->randomMachineName();

    $plugin_form = array(
      '#type' => $this->randomMachineName(),
    );

    $plugin = $this->getMockForAbstractClass('\Drupal\Tests\payment\Unit\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBaseUnitTestPluginFormPlugin');
    $plugin->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id));
    $plugin->expects($this->once())
      ->method('buildConfigurationForm')
      ->will($this->returnValue($plugin_form));

    $element = array(
      '#available_plugins' => array($plugin),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = [];

    $label = $this->randomMachineName();

    $this->sut->setLabel($label);

    $expected_build = array(
      '#available_plugins' => array($plugin),
      'select' => array(
        'message' => [
          '#title' => $label,
          '#type' => 'item',
        ],
        'container' => array(
          '#type' => 'container',
          'plugin_id' => array(
            '#type' => 'value',
            '#value' => $plugin_id,
          ),
        ),
      ),
      'plugin_form' => array(
        '#attributes' => array(
          'class' => array('payment-plugin-selector-' . Html::getId($this->pluginId) . '-payment-plugin-form'),
        ),
        '#type' => 'container',
      ) + $plugin_form,
    );
    $build = $this->sut->buildOneAvailablePlugin($element, $form_state, $form);
    unset($build['plugin_form']['#id']);
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::buildMultipleAvailablePlugins
   */
  public function testbuildMultipleAvailablePlugins() {
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');

    $element = array(
      '#available_plugins' => array($plugin),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = [];

    $plugin_form = array(
      '#type' => $this->randomMachineName(),
    );

    $selector = array(
      '#type' => $this->randomMachineName(),
    );

    /** @var \Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase|\PHPUnit_Framework_MockObject_MockObject $plugin_selector */
    $plugin_selector = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\PluginSelector\AdvancedPluginSelectorBase')
      ->setMethods(array('buildPluginForm', 'buildSelector'))
      ->setConstructorArgs(array([], $this->pluginId, $this->pluginDefinition, $this->stringTranslation, $this->responsePolicy))
      ->getMockForAbstractClass();
    $this->sut->setPluginManager($this->pluginManager, $this->mapper);
    $plugin_selector->expects($this->once())
      ->method('buildPluginForm')
      ->with($form_state)
      ->will($this->returnValue($plugin_form));
    $plugin_selector->expects($this->once())
      ->method('buildSelector')
      ->with($element, $form_state, array($plugin))
      ->will($this->returnValue($selector));
    $plugin_selector->setSelectedPlugin($plugin);

    $expected_build = array(
      '#available_plugins' => array($plugin),
      'select' => $selector,
        'plugin_form' => $plugin_form,
    );
    $this->assertEquals($expected_build, $plugin_selector->buildMultipleAvailablePlugins($element, $form_state, $form));
  }

  /**
   * @covers ::setSelectedPlugin
   * @covers ::getSelectedPlugin
   */
  public function testGetPlugin() {
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $this->assertSame($this->sut, $this->sut->setSelectedPlugin($plugin));
    $this->assertSame($plugin, $this->sut->getSelectedPlugin());
  }

  /**
   * @covers ::buildSelector
   */
  public function testBuildSelector() {
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $method = new \ReflectionMethod($this->sut, 'buildSelector');
    $method->setAccessible(TRUE);
    $get_element_id_method = new \ReflectionMethod($this->sut, 'getElementId');
    $get_element_id_method->setAccessible(TRUE);

    $plugin_id = $this->randomMachineName();
    $plugin_label = $this->randomMachineName();
    $plugin = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id));
    $plugin->expects($this->any())
      ->method('getPluginLabel')
      ->will($this->returnValue($plugin_label));

    $this->sut->setSelectedPlugin($plugin);

    $element = array(
      '#parents' => array('foo', 'bar'),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $available_plugins = array($plugin);

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
    $this->assertArrayHasKey('plugin_id', $build['container']);
    $this->assertEquals($expected_build_change, $build['container']['change']);
    $this->assertSame('container', $build['container']['#type']);
  }

}

/**
 * Provides a plugin that provides a form.
 */
abstract class AdvancedPluginSelectorBaseUnitTestPluginFormPlugin implements PluginInspectionInterface, PluginFormInterface {
}
