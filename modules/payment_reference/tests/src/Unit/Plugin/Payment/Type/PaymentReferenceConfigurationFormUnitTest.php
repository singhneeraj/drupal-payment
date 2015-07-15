<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_reference\Unit\Plugin\Payment\Type\PaymentReferenceConfigurationFormUnitTest.
 */

namespace Drupal\Tests\payment_reference\Unit\Plugin\Payment\Type {

  use Drupal\Core\Form\FormState;
  use Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm;
  use Drupal\plugin\PluginType;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm
   *
   * @group Payment Reference Field
   */
  class PaymentReferenceConfigurationFormUnitTest extends UnitTestCase {

    /**
     * The config factory used for testing.
     *
     * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configFactory;

    /**
     * The configuration the config factory returns.
     *
     * @see self::__construct
     *
     * @var array
     */
    protected $configFactoryConfiguration = [];

    /**
     * The form under test.
     *
     * @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm
     */
    protected $form;

    /**
     * The payment method manager used for testing.
     *
     * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodManager;

    /**
     * The plugin selector.
     *
     * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginSelector;

    /**
     * The plugin selector manager.
     *
     * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginSelectorManager;

    /**
     * The plugin selector plugin type.
     *
     * @var \Drupal\plugin\PluginTypeInterface
     */
    protected $pluginSelectorType;

    /**
     * The selected plugin selector.
     *
     * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectedPluginSelector;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface
     */
    protected $stringTranslation;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->configFactoryConfiguration = array(
        'payment_reference.payment_type' => array(
          'limit_allowed_plugins' => TRUE,
          'allowed_plugin_ids' => array($this->randomMachineName()),
          'plugin_selector_id' => $this->randomMachineName(),
        ),
      );

      $this->configFactory = $this->getConfigFactoryStub($this->configFactoryConfiguration);

      $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

      $this->pluginSelector = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface');

      $this->pluginSelectorManager = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface');

      $class_resolver = $this->getMock('\Drupal\Core\DependencyInjection\ClassResolverInterface');

      $this->stringTranslation = $this->getStringTranslationStub();

      $plugin_type_definition = [
        'id' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
        'provider' => $this->randomMachineName(),
      ];
      $this->pluginSelectorType = new PluginType($plugin_type_definition, $this->stringTranslation, $class_resolver, $this->pluginSelectorManager);

      $this->selectedPluginSelector = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface');

      $this->form = new PaymentReferenceConfigurationForm($this->configFactory, $this->stringTranslation, $this->paymentMethodManager, $this->pluginSelectorType);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $plugin_type_manager = $this->getMock('\Drupal\plugin\PluginTypeManagerInterface');
      $plugin_type_manager->expects($this->any())
        ->method('getPluginType')
        ->with('plugin.plugin_selector')
        ->willReturn($this->pluginSelectorType);

      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = array(
        array('config.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configFactory),
        array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
        ['plugin.plugin_type_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $plugin_type_manager],
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->will($this->returnValueMap($map));

      $form = PaymentReferenceConfigurationForm::create($container);
      $this->assertInstanceOf('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm', $form);
    }

    /**
     * @covers ::getFormId
     */
    public function testGetFormId() {
      $this->assertInternalType('string', $this->form->getFormId());
    }

    /**
     * @covers ::buildForm
     * @covers ::getPluginSelector
     */
    public function testBuildForm() {
      $form = [];
      $form_state = new FormState();

      $map = [
        ['payment_radios', [], $this->pluginSelector],
        [$this->configFactoryConfiguration['payment_reference.payment_type']['plugin_selector_id'], [], $this->selectedPluginSelector],
      ];
      $this->pluginSelectorManager->expects($this->atLeast(count($map)))
        ->method('createInstance')
        ->willReturnMap($map);

      $this->pluginSelector->expects($this->once())
        ->method('buildSelectorForm')
        ->with([], $form_state)
        ->willReturn($this->pluginSelector);

      $this->paymentMethodManager->expects($this->atLeastOnce())
        ->method('getDefinitions')
        ->willReturn([]);

      $build = $this->form->buildForm($form, $form_state);
      $this->assertInternalType('array', $build);
    }

    /**
     * @covers ::validateForm
     * @covers ::getPluginSelector
     */
    public function testValidateForm() {
      $form = [
        'plugin_selector' => [
          'foo' => $this->randomMachineName(),
        ],
      ];
      $form_state = new FormState();
      $form_state->setValues([
        'plugin_selector_id' => $this->configFactoryConfiguration['payment_reference.payment_type']['plugin_selector_id'],
        'allowed_plugin_ids' => $this->configFactoryConfiguration['payment_reference.payment_type']['allowed_plugin_ids'],
        'limit_allowed_plugins' => $this->configFactoryConfiguration['payment_reference.payment_type']['limit_allowed_plugins'],
      ]);

      $map = [
        ['payment_radios', [], $this->pluginSelector],
        [$this->configFactoryConfiguration['payment_reference.payment_type']['plugin_selector_id'], [], $this->selectedPluginSelector],
      ];
      $this->pluginSelectorManager->expects($this->atLeast(count($map)))
        ->method('createInstance')
        ->willReturnMap($map);

      $this->pluginSelector->expects($this->once())
        ->method('validateSelectorForm')
        ->with($form['plugin_selector'], $form_state);

      $this->form->validateForm($form, $form_state);
    }

    /**
     * @covers ::submitForm
     * @covers ::getPluginSelector
     */
    public function testSubmitForm() {
      $form = [
        'plugin_selector' => [
          'foo' => $this->randomMachineName(),
        ],
      ];
      $form_state = new FormState();
      $form_state->setValues([
        'plugin_selector_id' => $this->configFactoryConfiguration['payment_reference.payment_type']['plugin_selector_id'],
        'allowed_plugin_ids' => $this->configFactoryConfiguration['payment_reference.payment_type']['allowed_plugin_ids'],
        'limit_allowed_plugins' => $this->configFactoryConfiguration['payment_reference.payment_type']['limit_allowed_plugins'],
      ]);

      $map = [
        ['payment_radios', [], $this->pluginSelector],
        [$this->configFactoryConfiguration['payment_reference.payment_type']['plugin_selector_id'], [], $this->selectedPluginSelector],
      ];
      $this->pluginSelectorManager->expects($this->atLeast(count($map)))
        ->method('createInstance')
        ->willReturnMap($map);

      $this->pluginSelector->expects($this->once())
        ->method('submitSelectorForm')
        ->with($form['plugin_selector'], $form_state);
      $this->pluginSelector->expects($this->once())
        ->method('getSelectedPlugin')
        ->willReturn($this->selectedPluginSelector);

      $this->form->submitForm($form, $form_state);
    }

  }

}

namespace {

  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {
    }
  }

}

