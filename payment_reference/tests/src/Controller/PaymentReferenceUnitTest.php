<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Test\Controller\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests\Controller {

use Drupal\Core\Access\AccessInterface;
use Drupal\payment_reference\Controller\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_reference\Controller\PaymentReference
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The controller under test.
   *
   * @var \Drupal\payment_reference\Controller\PaymentReference
   */
  protected $controller;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityFormBuilder;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The payment reference queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $queue;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Controller\PaymentReference unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->entityFormBuilder = $this->getMock('\Drupal\Core\Entity\EntityFormBuilderInterface');

    $this->paymentLineItemManager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');

    $this->queue = $this->getMock('\Drupal\payment\QueueInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new PaymentReference($this->entityManager, $this->currentUser, $this->entityFormBuilder, $this->stringTranslation, $this->paymentLineItemManager, $this->queue);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      array('payment_reference.queue', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->queue),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = PaymentReference::create($container);
    $this->assertInstanceOf('\Drupal\payment_reference\Controller\PaymentReference', $form);
  }

  /**
   * @covers ::pay
   */
  public function testPay() {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();
    $currency_code = $this->randomName();
    $line_items_data = array(array(
      'plugin_configuration' => array(),
      'plugin_id' => $this->randomName(),
    ));

    $field_definition = $this->getMock('\Drupal\Core\Field\\FieldDefinitionInterface');
    $map = array(
      array('currency_code', $currency_code),
      array('line_items_data', $line_items_data),
    );
    $field_definition->expects($this->atLeastOnce())
      ->method('getSetting')
      ->will($this->returnValueMap($map));

    $definitions = array(
      $field_name => $field_definition,
    );

    $this->entityManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($definitions));

    $payment_type = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('setEntityTypeId')
      ->with($entity_type_id);
    $payment_type->expects($this->once())
      ->method('setBundle')
      ->with($bundle);
    $payment_type->expects($this->once())
      ->method('setFieldName')
      ->with($field_name);

    $payment_line_item = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');

    $this->paymentLineItemManager->expects($this->once())
      ->method('createInstance')
      ->with($line_items_data[0]['plugin_id'])
      ->will($this->returnValue($payment_line_item));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $payment->expects($this->once())
      ->method('setCurrencyCode')
      ->with($currency_code);
    $payment->expects($this->once())
      ->method('setLineItem')
      ->with($payment_line_item);

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

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($payment, 'payment_reference')
      ->will($this->returnValue($form));


    $this->assertSame($form, $this->controller->pay($entity_type_id, $bundle, $field_name));
  }

  /**
   * @covers ::payAccess
   * @covers ::fieldExists
   *
   * @dataProvider providerTestPayAccess
   */
  public function testPayAccess($expected, $entity_type_exists, $bundle_exists, $field_exists, $entity_access, $field_access, $queued_payments) {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();

    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');

    $user_id = mt_rand();
    $this->currentUser->expects($this->any())
      ->method('id')
      ->will($this->returnValue($user_id));

    $this->entityManager->expects($this->any())
      ->method('hasDefinition')
      ->with($entity_type_id)
      ->will($this->returnValue($entity_type_exists));
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($entity_type_id)
      ->will($this->returnValue($bundle_exists ? array(
        $bundle => array(),
      ) : array()));
    $this->entityManager->expects($this->any())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($field_exists ? array(
        $field_name => $field_definition,
      ) : array()));

    $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $payment_id = mt_rand();

    $access_controller = $this->getMock('\Drupal\Core\Entity\EntityAccessControllerInterface');
    $access_controller->expects($this->any())
      ->method('createAccess')
      ->with('payment_reference')
      ->will($this->returnValue($entity_access));
    $access_controller->expects($this->any())
      ->method('fieldAccess')
      ->with('edit', $field_definition)
      ->will($this->returnValue($field_access));
    $this->queue->expects($this->any())
      ->method('loadPaymentIds')
      ->will($this->returnValue($queued_payments ? array($payment_id) : array()));

    $this->entityManager->expects($this->any())
      ->method('getAccessController')
      ->will($this->returnValue($access_controller));

    $this->assertSame($expected, $this->controller->payAccess($request, $entity_type_id, $bundle, $field_name));
  }

  /**
   * Provides data to testPayAccess().
   */
  public function providerTestPayAccess() {
    return array(
      array(AccessInterface::ALLOW, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE),
      array(AccessInterface::DENY, FALSE, TRUE, TRUE, TRUE, TRUE, FALSE),
      array(AccessInterface::DENY, TRUE, FALSE, TRUE, TRUE, TRUE, FALSE),
      array(AccessInterface::DENY, TRUE, TRUE, FALSE, TRUE, TRUE, FALSE),
      array(AccessInterface::DENY, TRUE, TRUE, TRUE, FALSE, TRUE, FALSE),
      array(AccessInterface::DENY, TRUE, TRUE, TRUE, TRUE, FALSE, FALSE),
      array(AccessInterface::DENY, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE),
    );
  }

  /**
   * @covers ::payLabel
   */
  public function testPayLabel() {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();
    $label = $this->randomName();

    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->atLeastOnce())
      ->method('getLabel')
      ->will($this->returnValue($label));

    $field_definitions = array(
      $field_name => $field_definition,
    );

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($field_definitions));

    $this->assertSame($label, $this->controller->payLabel($entity_type_id, $bundle, $field_name));
  }

  /**
   * @covers ::resumeContext
   */
  public function testResumeContext() {
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
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();
    $label = $this->randomName();
    $field_definition = $this->getMock('\Drupal\Core\Field\\FieldDefinitionInterface');
    $field_definition->expects($this->atLeastOnce())
      ->method('getLabel')
      ->will($this->returnValue($label));

    $definitions = array(
      $field_name => $field_definition,
    );

    $this->entityManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($definitions));

    $payment_type = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('getEntityTypeId')
      ->will($this->returnValue($entity_type_id));
    $payment_type->expects($this->once())
      ->method('getBundle')
      ->will($this->returnValue($bundle));
    $payment_type->expects($this->once())
      ->method('getFieldName')
      ->will($this->returnValue($field_name));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $this->assertSame($label, $this->controller->resumeContextLabel($payment));
  }

  /**
   * @covers ::resumeContextAccess
   * @covers ::fieldExists
   *
   * @dataProvider providerTestResumeContextAccess
   */
  public function testResumeContextAccess($expected, $entity_type_exists, $bundle_exists, $field_exists) {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();

    $payment_type = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->will($this->returnValue($entity_type_id));
    $payment_type->expects($this->atLeastOnce())
      ->method('getBundle')
      ->will($this->returnValue($bundle));
    $payment_type->expects($this->atLeastOnce())
      ->method('getFieldName')
      ->will($this->returnValue($field_name));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');

    $this->entityManager->expects($this->any())
      ->method('hasDefinition')
      ->with($entity_type_id)
      ->will($this->returnValue($entity_type_exists));
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($entity_type_id)
      ->will($this->returnValue($bundle_exists ? array(
        $bundle => array(),
      ) : array()));
    $this->entityManager->expects($this->any())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($field_exists ? array(
        $field_name => $field_definition,
      ) : array()));

    $this->assertSame($expected, $this->controller->resumeContextAccess($payment));
  }

  /**
   * Provides data to testResumeCOntextAccess().
   */
  public function providerTestResumeContextAccess() {
    return array(
      array(AccessInterface::ALLOW, TRUE, TRUE, TRUE),
      array(AccessInterface::DENY, FALSE, TRUE, TRUE),
      array(AccessInterface::DENY, TRUE, FALSE, TRUE),
      array(AccessInterface::DENY, TRUE, TRUE, FALSE),
    );
  }

}

}

namespace {

if (!function_exists('drupal_get_path')) {
  function drupal_get_path() {
  }
}

}
