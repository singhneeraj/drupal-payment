<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_form\Unit\Plugin\Payment\Type\PaymentFormUnitTest.
 */

namespace Drupal\Tests\payment_form\Unit\Plugin\Payment\Type;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Payment\Type\PaymentForm
 *
 * @group Payment Form Field
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The event dispatcher used for testing.
   *
   * @var \Drupal\payment\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The payment used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment type plugin under test.
   *
   * @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm
   */
  protected $paymentType;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->eventDispatcher = $this->getMock('\Drupal\payment\EventDispatcherInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->paymentType = new PaymentForm([], 'payment_form', [], $this->eventDispatcher, $this->entityManager, $this->stringTranslation);

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($this->payment);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('payment.event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $plugin = PaymentForm::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_form\Plugin\Payment\Type\PaymentForm', $plugin);
  }

  /**
   * @covers ::setEntityTypeId
   * @covers ::getEntityTypeId
   */
  public function testGetEntityTypeId() {
    $id = $this->randomMachineName();
    $this->assertSame($this->paymentType, $this->paymentType->setEntityTypeId($id));
    $this->assertSame($id, $this->paymentType->getEntityTypeId());
  }

  /**
   * @covers ::setBundle
   * @covers ::getBundle
   */
  public function testGetBundle() {
    $bundle = $this->randomMachineName();
    $this->assertSame($this->paymentType, $this->paymentType->setBundle($bundle));
    $this->assertSame($bundle, $this->paymentType->getBundle());
  }

  /**
   * @covers ::setFieldName
   * @covers ::getFieldName
   */
  public function testGetFieldName() {
    $name = $this->randomMachineName();
    $this->assertSame($this->paymentType, $this->paymentType->setFieldName($name));
    $this->assertSame($name, $this->paymentType->getFieldName());
  }

  /**
   * @covers ::getPaymentDescription
   *
   * @depends testGetEntityTypeId
   * @depends testGetBundle
   * @depends testGetFieldName
   */
  public function testPaymentDescription() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();
    $field_name = $this->randomMachineName();
    $label = $this->randomMachineName();
    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->atLeastOnce())
      ->method('getLabel')
      ->will($this->returnValue($label));

    $definitions = array(
      $field_name => $field_definition,
    );

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($definitions));

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);
    $this->paymentType->setFieldName($field_name);

    $this->assertSame($label, $this->paymentType->getPaymentDescription());
  }

  /**
   * @covers ::getPaymentDescription
   */
  public function testGetPaymentDescriptionWithNonExistingField() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue([]));

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);

    $this->assertInstanceOf('\Drupal\Core\StringTranslation\TranslationWrapper', $this->paymentType->getPaymentDescription());
  }

  /**
   * @covers ::setDestinationUrl
   * @covers ::getDestinationUrl
   */
  public function testGetDestinationUrl() {
    $destination_url = $this->randomMachineName();
    $this->assertSame(spl_object_hash($this->paymentType), spl_object_hash($this->paymentType->setDestinationUrl($destination_url)));
    $this->assertSame($destination_url, $this->paymentType->getDestinationUrl());
  }

  /**
   * @covers ::resumeContextAccess
   */
  public function testResumeContextAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertTrue($this->paymentType->resumeContextAccess($account));
  }

  /**
   * @covers ::doGetResumeContextResponse
   * @depends testGetDestinationUrl
   */
  public function testDoGetResumeContextResponse() {
    $url = 'http://example.com/' . $this->randomMachineName();

    $this->paymentType->setDestinationUrl($url);

    $response = $this->paymentType->getResumeContextResponse();

    $this->assertInstanceOf('\Drupal\payment\Response\ResponseInterface', $response);
    $this->assertSame($url, $response->getRedirectUrl()->getUri());
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->paymentType->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('destination_url', $configuration);
    $this->assertInternalType('null', $configuration['destination_url']);
  }

}
