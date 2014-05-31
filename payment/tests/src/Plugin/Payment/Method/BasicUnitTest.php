<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Method\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\Basic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\Basic
 */
class BasicUnitTest extends PaymentMethodBaseUnitTestBase {

  /**
   * The payment method plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Basic
   */
  protected $paymentMethod;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Method\Basic unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    parent::setUp();

    $this->pluginDefinition += array(
      'entity_id' => $this->randomName(),
      'status' => $this->randomName(),
    );

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->paymentMethod = new Basic(array(), '', $this->pluginDefinition, $this->moduleHandler, $this->eventDispatcher, $this->token, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('token', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->token),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = Basic::create($container, array(), '', $this->pluginDefinition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\Method\Basic', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertInternalType('array', $this->paymentMethod->defaultConfiguration());
  }

  /**
   * @covers ::getStatus
   */
  public function testGetStatus() {
    $this->assertSame($this->pluginDefinition['status'], $this->paymentMethod->getStatus());
  }

  /**
   * @covers ::doExecutePayment
   */
  public function testDoExecutePayment() {
    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($this->pluginDefinition['status'])
      ->will($this->returnValue($payment_status));

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $payment_type->expects($this->once())
      ->method('resumeContext');

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('save');
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $payment->expects($this->once())
      ->method('setStatus')
      ->with($payment_status);

    $method = new \ReflectionMethod($this->paymentMethod, 'doExecutePayment');
    $method->setAccessible(TRUE);

    $method->invoke($this->paymentMethod, $payment);
  }

  /**
   * @covers ::getSupportedCurrencies
   */
  public function testGetSupportedCurrencies() {
    $this->assertTrue($this->paymentMethod->getSupportedCurrencies());
  }

  /**
   * @covers ::getEntityId
   */
  public function testGetEntityId() {
    $this->assertSame($this->pluginDefinition['entity_id'], $this->paymentMethod->getEntityId());
  }

}
