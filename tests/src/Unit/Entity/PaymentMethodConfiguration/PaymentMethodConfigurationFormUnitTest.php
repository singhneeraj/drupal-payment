<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\PaymentMethodConfiguration {

  use Drupal\Core\Form\FormState;
  use Drupal\Core\Url;
  use Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm
   *
   * @group Payment
   */
  class PaymentMethodConfigurationFormUnitTest extends UnitTestCase {

    /**
     * The current user.
     *
     * @var \Drupal\user\UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentUser;

    /**
     * The form under test.
     *
     * @var \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm
     */
    protected $form;

    /**
     * The form validator.
     *
     * @var \Drupal\Core\Form\FormValidatorInterface
     */
    protected $formValidator;

    /**
     * The payment method configuration.
     *
     * @var \Drupal\payment\Entity\PaymentMethodConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodConfiguration;

    /**
     * The payment method configuration manager.
     *
     * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodConfigurationManager;

    /**
     * The payment method configuration storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodConfigurationStorage;

    /**
     * The string translation service.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * {@inheritdoc}
     *
     * @covers ::__construct
     */
    public function setUp() {
      $this->currentUser = $this->getMock('\Drupal\user\UserInterface');

      $this->formValidator = $this->getMock('\Drupal\Core\Form\FormValidatorInterface');

      $this->paymentMethodConfigurationManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface');

      $this->paymentMethodConfigurationStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

      $this->paymentMethodConfiguration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
        ->disableOriginalConstructor()
        ->getMock();

      $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
      $this->stringTranslation->expects($this->any())
        ->method('translate')
        ->will($this->returnArgument(0));

      $container = new ContainerBuilder();
      $container->set('form_validator', $this->formValidator);
      \Drupal::setContainer($container);

      $this->form = new PaymentMethodConfigurationForm($this->stringTranslation, $this->currentUser, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationManager);
      $this->form->setEntity($this->paymentMethodConfiguration);
    }

    /**
     * @covers ::create
     */
    function testCreate() {
      $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
      $map = array(
        array('payment_method_configuration', $this->paymentMethodConfigurationStorage),
      );
      $entity_manager->expects($this->atLeast(count($map)))
        ->method('getStorage')
        ->will($this->returnValueMap($map));

      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = array(
        array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
        array('plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->will($this->returnValueMap($map));

      $form = PaymentMethodConfigurationForm::create($container);
      $this->assertInstanceOf('\Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm', $form);
    }

    /**
     * @covers ::form
     *
     * @dataProvider providerTestForm
     */
    public function testForm($has_owner) {
      $current_user_label = $this->randomMachineName();

      $payment_method_configuration_entity_id = $this->randomMachineName();
      $payment_method_configuration_entity_is_new = FALSE;
      $payment_method_configuration_entity_label = $this->randomMachineName();
      $payment_method_configuration_entity_status = TRUE;
      $payment_method_configuration_plugin_form = array(
        '#type' => $this->randomMachineName(),
      );
      $payment_method_configuration_plugin_id = $this->randomMachineName();
      $payment_method_configuration_plugin_configuration = array(
        'foo' => $this->randomMachineName(),
      );
      $payment_method_configuration_plugin_label = $this->randomMachineName();
      $payment_method_configuration_plugin_definition = array(
        'label' => $payment_method_configuration_plugin_label,
      );

      $owner = $this->getMock('\Drupal\user\UserInterface');

      $payment_method_configuration_plugin = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface');

      $form = array(
        'plugin_form' => [],
      );
      $form_state = new FormState();

      $payment_method_configuration_plugin->expects($this->atLeastOnce())
        ->method('buildConfigurationForm')
        ->with([], $form_state)
        ->will($this->returnValue($payment_method_configuration_plugin_form));

      $this->paymentMethodConfigurationManager->expects($this->atLeastOnce())
        ->method('getDefinition')
        ->will($this->returnValue($payment_method_configuration_plugin_definition));

      $language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

      $this->paymentMethodConfiguration->expects($this->atLeastOnce())
        ->method('getOwner')
        ->willReturn($has_owner ? $owner : NULL);
      $this->paymentMethodConfiguration->expects($this->atLeastOnce())
        ->method('getPluginConfiguration')
        ->will($this->returnValue($payment_method_configuration_plugin_configuration));
      $this->paymentMethodConfiguration->expects($this->any())
        ->method('getPluginId')
        ->will($this->returnValue($payment_method_configuration_plugin_id));
      $this->paymentMethodConfiguration->expects($this->any())
        ->method('id')
        ->will($this->returnValue($payment_method_configuration_entity_id));
      $this->paymentMethodConfiguration->expects($this->any())
        ->method('label')
        ->will($this->returnValue($payment_method_configuration_entity_label));
      $this->paymentMethodConfiguration->expects($this->any())
        ->method('language')
        ->will($this->returnValue($language));
      $this->paymentMethodConfiguration->expects($this->any())
        ->method('status')
        ->will($this->returnValue($payment_method_configuration_entity_status));

      $this->paymentMethodConfigurationManager->expects($this->once())
        ->method('createInstance')
        ->with($payment_method_configuration_plugin_id, $payment_method_configuration_plugin_configuration)
        ->will($this->returnValue($payment_method_configuration_plugin));

      $build = $this->form->form($form, $form_state);
      // Make sure the payment method configuration plugin is instantiated only
      // once by building the form twice.
      $this->form->form($form, $form_state);
      unset($build['#process']);
      unset($build['langcode']);
      $expected_build = array(
        'type' => array(
          '#type' => 'item',
          '#title' => 'Type',
          '#markup' => $payment_method_configuration_plugin_label,
        ),
        'status' => array(
          '#type' => 'checkbox',
          '#title' => 'Enabled',
          '#default_value' => $payment_method_configuration_entity_status,
        ),
        'label' => array(
          '#type' => 'textfield',
          '#title' => 'Label',
          '#default_value' => $payment_method_configuration_entity_label,
          '#maxlength' => 255,
          '#required' => TRUE,
        ),
        'id' => array(
          '#type' => 'machine_name',
          '#default_value' => $payment_method_configuration_entity_id,
          '#maxlength' => 255,
          '#required' => TRUE,
          '#machine_name' => array(
            'source' => array('label'),
            'exists' => array($this->form, 'paymentMethodConfigurationIdExists'),
          ),
          '#disabled' => !$payment_method_configuration_entity_is_new,
        ),
        'owner' => array(
          '#target_type' => 'user',
          '#type' => 'entity_autocomplete',
          '#title' => 'Owner',
          '#default_value' => $has_owner ? $owner : $this->currentUser,
          '#required' => TRUE,
        ),
        'plugin_form' => array(
            '#tree' => TRUE,
          ) + $payment_method_configuration_plugin_form,
      );
      $this->assertEquals($expected_build, $build);
    }

    /**
     * Provides data to self::testForm().
     */
    public function providerTestForm() {
      return [
        [TRUE],
        [FALSE],
      ];
    }

    /**
     * @covers ::copyFormValuesToEntity
     */
    public function testCopyFormValuesToEntity() {
      $label = $this->randomMachineName();
      $owner_id = mt_rand();
      $plugin_configuration = array(
        'bar' => $this->randomMachineName(),
      );
      $status = TRUE;

      $this->paymentMethodConfiguration->expects($this->once())
        ->method('setLabel')
        ->with($label);
      $this->paymentMethodConfiguration->expects($this->once())
        ->method('setOwnerId')
        ->with($owner_id);
      $this->paymentMethodConfiguration->expects($this->once())
        ->method('setPluginConfiguration')
        ->with($plugin_configuration);
      $this->paymentMethodConfiguration->expects($this->once())
        ->method('setStatus')
        ->with($status);

      $owner = $this->getMockBuilder('\Drupal\user\Entity\User')
        ->disableOriginalConstructor()
        ->getMock();
      $owner->expects($this->any())
        ->method('id')
        ->will($this->returnValue($owner_id));

      $plugin = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface');
      $plugin->expects($this->atLeastOnce())
        ->method('getConfiguration')
        ->will($this->returnValue($plugin_configuration));

      $form = [];
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $map = array(
        array('payment_method_configuration', $plugin),
        array('values', array(
          'label' => $label,
          'owner' => $owner_id,
          'status' => $status,
        )),
      );
      $form_state->expects($this->atLeastOnce())
        ->method('get')
        ->willReturnMap($map);
      $form_state->expects($this->atLeastOnce())
        ->method('getValues')
        ->willReturn(array(
          'label' => $label,
          'owner' => $owner_id,
          'status' => $status,
        ));

      $method = new \ReflectionMethod($this->form, 'copyFormValuesToEntity');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->form, array($this->paymentMethodConfiguration, $form, $form_state));
    }

    /**
     * @covers ::paymentMethodConfigurationIdExists
     */
    public function testPaymentMethodConfigurationIdExists() {
      $payment_method_configuration_id = $this->randomMachineName();

      $this->paymentMethodConfigurationStorage->expects($this->at(0))
        ->method('load')
        ->with($payment_method_configuration_id)
        ->will($this->returnValue($this->paymentMethodConfiguration));
      $this->paymentMethodConfigurationStorage->expects($this->at(1))
        ->method('load')
        ->with($payment_method_configuration_id)
        ->will($this->returnValue(NULL));

      $this->assertTrue($this->form->paymentMethodConfigurationIdExists($payment_method_configuration_id));
      $this->assertFalse($this->form->paymentMethodConfigurationIdExists($payment_method_configuration_id));
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $url = new Url($this->randomMachineName());

      $this->paymentMethodConfiguration->expects($this->once())
        ->method('save');
      $this->paymentMethodConfiguration->expects($this->atLeastOnce())
        ->method('urlinfo')
        ->with('collection')
        ->willReturn($url);

      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRedirectUrl')
        ->with($url);

      /** @var \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm|\PHPUnit_Framework_MockObject_MockObject $form */
      $form = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm')
        ->setConstructorArgs(array($this->stringTranslation, $this->currentUser, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationManager))
        ->setMethods(array('copyFormValuesToEntity'))
        ->getMock();
      $form->setEntity($this->paymentMethodConfiguration);

      $form->save([], $form_state);
    }

    /**
     * @covers ::submitForm
     */
    public function testSubmitForm() {
      /** @var \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm|\PHPUnit_Framework_MockObject_MockObject $form_object */
      $form_object = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm')
        ->setConstructorArgs(array($this->stringTranslation, $this->currentUser, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationManager))
        ->setMethods(array('copyFormValuesToEntity'))
        ->getMock();
      $form_object->setEntity($this->paymentMethodConfiguration);

      $payment_method_configuration_plugin = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface');

      $form = array(
        'plugin_form' => array(
          '#type' => $this->randomMachineName(),
        ),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $map = array(
        array('payment_method_configuration', $payment_method_configuration_plugin),
      );
      $form_state->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $payment_method_configuration_plugin->expects($this->once())
        ->method('submitConfigurationForm')
        ->with($form['plugin_form'], $form_state);

      $form_object->submitForm($form, $form_state);
    }

  }

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
