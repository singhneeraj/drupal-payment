<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentStatus\PaymentStatusFormUnitTest.
 */

namespace Drupal\payment\Tests\Entity\PaymentStatus {

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
    $label = $this->randomName();
    $id = $this->randomName();
    $is_new = FALSE;
    $parent_id = $this->randomName();
    $description = $this->randomName();

    $form_state = array();

    $language = $this->getMockBuilder('\Drupal\Core\Language\Language')
      ->disableOriginalConstructor()
      ->getMock();

    $options = array(
      'foo' => $this->randomName(),
      'bar' => $this->randomName(),
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

    $build = $this->form->form(array(), $form_state);
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
    $description = $this->randomName();
    $id = $this->randomName();
    $label = $this->randomName();
    $parent_id = $this->randomName();

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

    $form = array();
    $form_state = array(
      'values' => array(
        'description' => $description,
        'id' => $id,
        'label' => $label,
        'parent_id' => $parent_id,
      ),
    );

    $method = new \ReflectionMethod($this->form, 'copyFormValuesToEntity');
    $method->setAccessible(TRUE);

    $method->invokeArgs($this->form, array($this->paymentStatus, $form, &$form_state));
  }

  /**
   * @covers ::paymentStatusIdExists
   */
  public function testPaymentStatusIdExists() {
    $method = new \ReflectionMethod($this->form, 'paymentStatusIdExists');
    $method->setAccessible(TRUE);

    $payment_method_configuration_id = $this->randomName();

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
    $form_state = array();

    /** @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm|\PHPUnit_Framework_MockObject_MockObject $form */
    $form = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus\PaymentStatusForm')
      ->setConstructorArgs(array($this->stringTranslation, $this->paymentStatusStorage, $this->paymentStatusManager))
      ->setMethods(array('copyFormValuesToEntity'))
      ->getMock();
    $form->setEntity($this->paymentStatus);

    $this->paymentStatus->expects($this->once())
      ->method('save');

    $form->save(array(), $form_state);
    $this->assertArrayHasKey('redirect_route', $form_state);
    /** @var \Drupal\Core\Url $url */
    $url = $form_state['redirect_route'];
    $this->assertInstanceOf('\Drupal\Core\Url', $url);
    $this->assertSame('payment.payment_status.list', $url->getRouteName());
  }

}

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}
if (!function_exists('form_execute_handlers')) {
  function form_execute_handlers() {}
}

}
