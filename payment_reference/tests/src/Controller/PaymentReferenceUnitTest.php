<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Test\Controller\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Test\Controller;

use Drupal\Core\Access\AccessInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_reference\Controller\PaymentReference
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formBuilder;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The controller under test.
   *
   * @var \Drupal\payment_reference\Controller\PaymentReference|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $controller;

  /**
   * The payment reference queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $queue;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\PaymentReference unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityManager = $this->getMockBuilder('\Drupal\Core\Entity\EntityManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->currentUser = $this->getMockBuilder('\Drupal\Core\Session\AccountInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->formBuilder = $this->getMockBuilder('\Drupal\Core\Form\FormBuilder')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentLineItemManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->queue = $this->getMockBuilder('\Drupal\payment\QueueInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->controller = $this->getMockBuilder('\Drupal\payment_reference\Controller\PaymentReference')
      ->setMethods(array('currentUser', 'entityManager', 't'))
      ->setConstructorArgs(array($this->formBuilder, $this->paymentLineItemManager, $this->queue))
      ->getMock();
    $this->controller->expects($this->any())
      ->method('entityManager')
      ->will($this->returnValue($this->entityManager));
    $this->controller->expects($this->any())
      ->method('currentUser')
      ->will($this->returnValue($this->currentUser));
  }

  /**
   * @covers ::pay
   */
  public function testPay() {
    $payment_type = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('setFieldInstanceConfigId')
      ->will($this->returnSelf());

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $payment->expects($this->once())
      ->method('setCurrencyCode')
      ->will($this->returnSelf());

    $storage_controller = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage_controller->expects($this->once())
      ->method('create')
      ->will($this->returnValue($payment));

    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($this->equalTo('payment'))
      ->will($this->returnValue($storage_controller));

    $form = $this->getMockBuilder('\Drupal\payment_reference\Entity\PaymentFormController')
    ->disableOriginalConstructor()
      ->getMock();

    $this->entityManager->expects($this->once())
      ->method('getFormObject')
      ->will($this->returnValue($form));

    $payment_line_item = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');

    $this->paymentLineItemManager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($payment_line_item));

    $field_instance_config = $this->getMock('\Drupal\field\FieldInstanceConfigInterface');
    $field_instance_config->expects($this->once())
      ->method('id')
      ->will($this->returnValue($this->randomName()));
    $map = array(
      array('currency_code', $this->randomName()),
      array('line_items_data', array(array(
        'plugin_configuration' => array(),
        'plugin_id' => '',
      ))),
    );
    $field_instance_config->expects($this->exactly(2))
      ->method('getSetting')
      ->will($this->returnValueMap($map));

    $this->controller->pay($field_instance_config);
  }

  /**
   * @covers ::payAccess
   */
  public function testPayAccess() {
    $user_id = mt_rand();
    $this->currentUser->expects($this->exactly(4))
      ->method('id')
      ->will($this->returnValue($user_id));

    $field_instance_config_id = $this->randomName();
    $field_instance_config = $this->getMock('\Drupal\field\FieldInstanceConfigInterface');
    $field_instance_config->expects($this->any())
      ->method('id')
      ->will($this->returnValue($field_instance_config_id));

    $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $payment_id = mt_rand();

    $access_controller = $this->getMock('\Drupal\Core\Entity\EntityAccessControllerInterface');
    // Test a payment without create access, without queued payments.
    $access_controller->expects($this->at(0))
      ->method('createAccess')
      ->will($this->returnValue(FALSE));
    $this->queue->expects($this->at(0))
      ->method('loadPaymentIds')
      ->will($this->returnValue(array()));
    // Test a payment with create access, without queued payments.
    $access_controller->expects($this->at(1))
      ->method('createAccess')
      ->will($this->returnValue(TRUE));
    $this->queue->expects($this->at(1))
      ->method('loadPaymentIds')
      ->will($this->returnValue(array()));
    // Test a payment without create access, with queued payments.
    $access_controller->expects($this->at(2))
      ->method('createAccess')
      ->will($this->returnValue(FALSE));
    $this->queue->expects($this->at(2))
      ->method('loadPaymentIds')
      ->will($this->returnValue(array($payment_id)));
    // Test a payment with create access, with queued payments.
    $access_controller->expects($this->at(3))
      ->method('createAccess')
      ->will($this->returnValue(TRUE));
    $this->queue->expects($this->at(3))
      ->method('loadPaymentIds')
      ->will($this->returnValue(array($payment_id)));

    $this->entityManager->expects($this->any())
      ->method('getAccessController')
      ->will($this->returnValue($access_controller));

    // Test a payment without create access, without queued payments.
    $this->assertSame(AccessInterface::DENY, $this->controller->payAccess($request, $field_instance_config));

    // Test a payment with create access, without queued payments.
    $this->assertSame(AccessInterface::ALLOW, $this->controller->payAccess($request, $field_instance_config));

    // Test a payment without create access, with queued payments.
    $this->assertSame(AccessInterface::DENY, $this->controller->payAccess($request, $field_instance_config));

    // Test a payment with create access, with queued payments.
    $this->assertSame(AccessInterface::DENY, $this->controller->payAccess($request, $field_instance_config));
  }

  /**
   * @covers ::payLabel
   */
  public function testPayLabel() {
    $label = $this->randomName();
    $field_instance_config = $this->getMock('\Drupal\field\FieldInstanceConfigInterface');
    $field_instance_config->expects($this->once())
      ->method('label')
      ->will($this->returnValue($label));

    $this->assertSame($label, $this->controller->payLabel($field_instance_config));
  }

  /**
   * @covers ::resumeContext
   */
  public function testResumeContext() {
    $this->controller = $this->getMockBuilder('\Drupal\payment_reference\Controller\PaymentReference')
      ->disableOriginalConstructor()
      ->setMethods(array('drupalGetPath', 't'))
      ->getMock();
    $this->controller->expects($this->once())
      ->method('drupalGetPath')
      ->will($this->returnValue($this->randomName()));

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('access')
      ->with('view')
      ->will($this->returnValue(TRUE));
    $payment->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue($payment_status));

    $this->controller->resumeContext($payment);
  }

  /**
   * @covers ::resumeContextLabel
   */
  public function testResumeContextLabel() {
    $field_instance_config_label = $this->randomName();
    $field_instance_config_id = $this->randomName();
    $field_instance_config = $this->getMock('\Drupal\field\FieldInstanceConfigInterface');
    $field_instance_config->expects($this->once())
      ->method('label')
      ->will($this->returnValue($field_instance_config_label));

    $storage_controller = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage_controller->expects($this->once())
      ->method('load')
      ->with($field_instance_config_id)
      ->will($this->returnValue($field_instance_config));

    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->will($this->returnValue($storage_controller));

    $payment_type = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('getFieldInstanceConfigId')
      ->will($this->returnValue($field_instance_config_id));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $this->assertSame($field_instance_config_label, $this->controller->resumeContextLabel($payment));
  }

}
