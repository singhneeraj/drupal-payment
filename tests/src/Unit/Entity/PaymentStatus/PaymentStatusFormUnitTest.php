<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentStatus\PaymentStatusFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\PaymentStatus {

  use Drupal\Core\Form\FormState;
  use Drupal\payment\Entity\PaymentStatus\PaymentStatusForm;
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
    protected $form;

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

      $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

      $this->paymentStatusStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

      $this->paymentStatus = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
        ->disableOriginalConstructor()
        ->getMock();

      $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
      $this->stringTranslation->expects($this->any())
        ->method('translate')
        ->will($this->returnArgument(0));

      $this->form = new PaymentStatusForm($this->stringTranslation, $this->paymentStatusStorage, $this->paymentStatusManager);
      $this->form->setEntity($this->paymentStatus);
    }

    /**
     * @covers ::create
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
        array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
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

      $options = array(
        'foo' => $this->randomMachineName(),
        'bar' => $this->randomMachineName(),
      );

      $this->paymentStatus->expects($this->any())
        ->method('id')
        ->will($this->returnValue($id));
      $this->paymentStatus->expects($this->any())
        ->method('getDescription')
        ->will($this->returnValue($description));
      $this->paymentStatus->expects($this->any())
        ->method('getParentId')
        ->will($this->returnValue($parent_id));
      $this->paymentStatus->expects($this->any())
        ->method('isNew')
        ->will($this->returnValue($is_new));
      $this->paymentStatus->expects($this->any())
        ->method('label')
        ->will($this->returnValue($label));
      $this->paymentStatus->expects($this->any())
        ->method('language')
        ->will($this->returnValue($language));

      $this->paymentStatusManager->expects($this->once())
        ->method('options')
        ->will($this->returnValue($options));

      $build = $this->form->form([], $form_state);
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
            'exists' => array($this->form, 'PaymentStatusIdExists'),
          ),
          '#maxlength' => 255,
          '#type' => 'machine_name',
          '#required' => TRUE,
        ),
        'parent_id' => array(
          '#default_value' => $parent_id,
          '#empty_value' => '',
          '#options' => $options,
          '#title' => 'Parent status',
          '#type' => 'select',
        ),
        'description' => array(
          '#type' => 'textarea',
          '#title' => 'Description',
          '#default_value' => $description,
          '#maxlength' => 255,
        ),
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

      $form = [];
      $form_state = new FormState();
      $form_state->setValue('description', $description);
      $form_state->setValue('id', $id);
      $form_state->setValue('label', $label);
      $form_state->setValue('parent_id', $parent_id);

      $method = new \ReflectionMethod($this->form, 'copyFormValuesToEntity');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->form, array($this->paymentStatus, $form, $form_state));
    }

    /**
     * @covers ::paymentStatusIdExists
     */
    public function testPaymentStatusIdExists() {
      $method = new \ReflectionMethod($this->form, 'paymentStatusIdExists');
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

      $this->assertTrue($method->invoke($this->form, $payment_method_configuration_id));
      $this->assertFalse($method->invoke($this->form, $payment_method_configuration_id));
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRedirect')
        ->with('payment.payment_status.list');

      /** @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm|\PHPUnit_Framework_MockObject_MockObject $form */
      $form = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus\PaymentStatusForm')
        ->setConstructorArgs(array($this->stringTranslation, $this->paymentStatusStorage, $this->paymentStatusManager))
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
