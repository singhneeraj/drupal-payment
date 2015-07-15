<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentStatus\PaymentStatusFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\PaymentStatus {

  use Drupal\Core\Form\FormState;
  use Drupal\payment\Entity\PaymentStatus\PaymentStatusForm;
  use Drupal\plugin\PluginType;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm
   *
   * @group Payment
   */
  class PaymentStatusFormUnitTest extends UnitTestCase {

    /**
     * The form under test.
     *
     * @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm
     */
    protected $sut;

    /**
     * The payment status.
     *
     * @var \Drupal\payment\Entity\PaymentStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentStatus;

    /**
     * The payment method configuration manager.
     *
     * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentStatusManager;

    /**
     * The payment status storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentStatusStorage;

    /**
     * The plugin selector manager.
     *
     * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginSelectorManager;

    /**
     * The plugin type manager.
     *
     * @var \Drupal\plugin\PluginTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginTypeManager;

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
      $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

      $this->paymentStatusStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

      $this->paymentStatus = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
        ->disableOriginalConstructor()
        ->getMock();

      $this->pluginSelectorManager = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface');

      $class_resolver = $this->getMock('\Drupal\Core\DependencyInjection\ClassResolverInterface');

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->pluginTypeManager = $this->getMock('\Drupal\plugin\PluginTypeManagerInterface');
      $plugin_type_definition = [
        'id' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
        'provider' => $this->randomMachineName(),
      ];
      $plugin_type = new PluginType($plugin_type_definition, $this->stringTranslation, $class_resolver, $this->paymentStatusManager);
      $this->pluginTypeManager->expects($this->any())
        ->method('getPluginType')
        ->with('payment.status')
        ->willReturn($plugin_type);

      $this->sut = new PaymentStatusForm($this->stringTranslation, $this->paymentStatusStorage, $this->pluginSelectorManager, $this->pluginTypeManager);
      $this->sut->setEntity($this->paymentStatus);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
      $entity_manager->expects($this->any())
        ->method('getStorage')
        ->with('payment_status')
        ->will($this->returnValue($this->paymentStatusStorage));

      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = array(
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
        array('plugin.manager.plugin.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager),
        array('plugin.plugin_type_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginTypeManager),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->will($this->returnValueMap($map));

      $form = PaymentStatusForm::create($container);
      $this->assertInstanceOf('\Drupal\payment\Entity\PaymentStatus\PaymentStatusForm', $form);
    }

    /**
     * @covers ::form
     */
    public function testForm() {
      $label = $this->randomMachineName();
      $id = $this->randomMachineName();
      $is_new = FALSE;
      $parent_id = $this->randomMachineName();
      $description = $this->randomMachineName();

      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

      $parent_selector_form = [
        '#foo' => $this->randomMachineName(),
      ];

      $parent_selector = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface');
      $parent_selector->expects($this->atLeastOnce())
        ->method('buildSelectorForm')
        ->with([], $form_state)
        ->willReturn($parent_selector_form);

      $this->pluginSelectorManager->expects($this->atLeastOnce())
        ->method('createInstance')
        ->willReturn($parent_selector);

      $this->paymentStatus->expects($this->any())
        ->method('id')
        ->willReturn($id);
      $this->paymentStatus->expects($this->any())
        ->method('getDescription')
        ->willReturn($description);
      $this->paymentStatus->expects($this->any())
        ->method('getParentId')
        ->willReturn($parent_id);
      $this->paymentStatus->expects($this->any())
        ->method('isNew')
        ->willReturn($is_new);
      $this->paymentStatus->expects($this->any())
        ->method('label')
        ->willReturn($label);
      $this->paymentStatus->expects($this->any())
        ->method('language')
        ->willReturn($language);

      $build = $this->sut->form([], $form_state);
      unset($build['#process']);
      unset($build['langcode']);
      $expected_build = array(
        'label' => array(
          '#type' => 'textfield',
          '#title' => 'Label',
          '#default_value' => $label,
          '#maxlength' => 255,
          '#required' => TRUE,
        ),
        'id' => array(
          '#default_value' => $id,
          '#disabled' => !$is_new,
          '#machine_name' => array(
            'source' => array('label'),
            'exists' => array($this->sut, 'PaymentStatusIdExists'),
          ),
          '#maxlength' => 255,
          '#type' => 'machine_name',
          '#required' => TRUE,
        ),
        'parent_id' => $parent_selector_form,
        'description' => array(
          '#type' => 'textarea',
          '#title' => 'Description',
          '#default_value' => $description,
          '#maxlength' => 255,
        ),
        '#after_build' => ['::afterBuild'],
      );
      $this->assertSame($expected_build, $build);
    }

    /**
     * @covers ::copyFormValuesToEntity
     */
    public function testCopyFormValuesToEntity() {
      $description = $this->randomMachineName();
      $id = $this->randomMachineName();
      $label = $this->randomMachineName();
      $parent_id = $this->randomMachineName();

      $this->paymentStatus->expects($this->once())
        ->method('setDescription')
        ->with($description);
      $this->paymentStatus->expects($this->once())
        ->method('setId')
        ->with($id);
      $this->paymentStatus->expects($this->once())
        ->method('setLabel')
        ->with($label);
      $this->paymentStatus->expects($this->once())
        ->method('setParentId')
        ->with($parent_id);

      $parent_status = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface');
      $parent_status->expects($this->atLeastOnce())
        ->method('getPluginId')
        ->willReturn($parent_id);

      $parent_selector = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface');
      $parent_selector->expects($this->atLeastOnce())
        ->method('getSelectedPlugin')
        ->willReturn($parent_status);

      $this->pluginSelectorManager->expects($this->atLeastOnce())
        ->method('createInstance')
        ->willReturn($parent_selector);

      $form = [];
      $form_state = new FormState();
      $form_state->setValue('description', $description);
      $form_state->setValue('id', $id);
      $form_state->setValue('label', $label);
      $form_state->setValue('parent_id', $parent_id);

      $method = new \ReflectionMethod($this->sut, 'copyFormValuesToEntity');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->sut, array($this->paymentStatus, $form, $form_state));
    }

    /**
     * @covers ::paymentStatusIdExists
     */
    public function testPaymentStatusIdExists() {
      $method = new \ReflectionMethod($this->sut, 'paymentStatusIdExists');
      $method->setAccessible(TRUE);

      $payment_method_configuration_id = $this->randomMachineName();

      $this->paymentStatusStorage->expects($this->at(0))
        ->method('load')
        ->with($payment_method_configuration_id)
        ->will($this->returnValue($this->paymentStatus));
      $this->paymentStatusStorage->expects($this->at(1))
        ->method('load')
        ->with($payment_method_configuration_id)
        ->will($this->returnValue(NULL));

      $this->assertTrue($method->invoke($this->sut, $payment_method_configuration_id));
      $this->assertFalse($method->invoke($this->sut, $payment_method_configuration_id));
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRedirect')
        ->with('entity.payment_status.collection');

      /** @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm|\PHPUnit_Framework_MockObject_MockObject $form */
      $form = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus\PaymentStatusForm')
        ->setConstructorArgs(array($this->stringTranslation, $this->paymentStatusStorage, $this->pluginSelectorManager, $this->pluginTypeManager))
        ->setMethods(array('copyFormValuesToEntity'))
        ->getMock();
      $form->setEntity($this->paymentStatus);

      $this->paymentStatus->expects($this->once())
        ->method('save');

      $form->save([], $form_state);
    }

  }

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
