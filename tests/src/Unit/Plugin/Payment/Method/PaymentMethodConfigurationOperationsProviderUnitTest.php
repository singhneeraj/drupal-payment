<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Status\PaymentMethodConfigurationOperationsProviderUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $redirectDestination;

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
    $this->paymentMethodConfigurationListBuilder = $this->getMock('\Drupal\Core\Entity\EntityListBuilderInterface');

    $this->paymentMethodConfigurationStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->redirectDestination = $this->getMock('\Drupal\Core\Routing\RedirectDestinationInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->provider = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider')
      ->setConstructorArgs([$this->stringTranslation, $this->redirectDestination, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationListBuilder])
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   * @covers ::__construct
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
      array('redirect.destination', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->redirectDestination),
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

    $this->redirectDestination->expects($this->atLeastOnce())
      ->method('get')
      ->willReturn($destination);

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
