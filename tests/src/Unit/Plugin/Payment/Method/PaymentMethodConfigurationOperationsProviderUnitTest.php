<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Status\PaymentMethodConfigurationOperationsProviderUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider
 *
 * @group Payment
 */
class PaymentMethodConfigurationOperationsProviderUnitTest extends UnitTestCase {

  /**
   * The payment method configuration list builder.
   *
   * @var \Drupal\Core\Entity\EntityListBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationListBuilder;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * The provider under test
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $provider;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

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
    $this->paymentMethodConfigurationListBuilder = $this->getMock('\Drupal\Core\Entity\EntityListBuilderInterface');

    $this->paymentMethodConfigurationStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->provider = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider')
      ->setConstructorArgs(array($this->requestStack, $this->stringTranslation, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationListBuilder))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getListBuilder')
      ->with('payment_method_configuration')
      ->will($this->returnValue($this->paymentMethodConfigurationListBuilder));
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment_method_configuration')
      ->will($this->returnValue($this->paymentMethodConfigurationStorage));

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider $class_name */
    $class_name = get_class($this->provider);
    $provider = $class_name::create($container);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider', $provider);
  }

  /**
   * @covers ::getOperations
   */
  public function testGetOperations() {
    $list_builder_operations = array(
      'edit' => array(
      ),
      'delete' => array(
      ),
      'enable' => array(
      ),
      'disable' => array(
      ),
      'foo' => array(
      ),
    );

    $destination = $this->randomMachineName();
    $attributes = new ParameterBag();
    $attributes->set('_system_path', $destination);
    /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit_Framework_MockObject_MockObject $request */
    $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
    $request->attributes = new ParameterBag();
    $request->attributes->set('_system_path', $destination);
    $this->requestStack->expects($this->atLeastOnce())
      ->method('getCurrentRequest')
      ->will($this->returnValue($request));

    $plugin_id = $this->randomMachineName();

    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();

    $this->provider->expects($this->once())
      ->method('getPaymentMethodConfiguration')
      ->with($plugin_id)
      ->will($this->returnValue($payment_method_configuration));

    $this->paymentMethodConfigurationListBuilder->expects($this->once())
      ->method('getOperations')
      ->with($payment_method_configuration)
      ->will($this->returnValue($list_builder_operations));

    $expected_operations = $list_builder_operations;
    unset($expected_operations['foo']);
    foreach ($expected_operations as $name => $expected_operation) {
      $expected_operations[$name]['title'] = NULL;
      $expected_operations[$name]['query']['destination'] = $destination;
    }
    $this->assertEquals($expected_operations, $this->provider->getOperations($plugin_id));
  }

}
