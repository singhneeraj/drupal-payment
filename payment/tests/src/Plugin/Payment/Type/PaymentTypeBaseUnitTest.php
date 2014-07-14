<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Type\PaymentTypeBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Type;

use Drupal\payment\Event\PaymentEvents;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Type\PaymentTypeBase
 *
 * @group Payment
 */
class PaymentTypeBaseUnitTest extends UnitTestCase {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The payment type under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentType;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $configuration = array();
    $plugin_id = $this->randomName();
    $plugin_definition = array();
    $this->paymentType = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Type\PaymentTypeBase')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->eventDispatcher))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   */
  public function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase $class_name */
    $class_name = get_class($this->paymentType);

    $line_item = $class_name::create($container, array(), $this->randomName(), array());
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\type\PaymentTypeBase', $line_item);
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame(array(), $this->paymentType->calculateDependencies());
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertSame(array(), $this->paymentType->defaultConfiguration());
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      'foo' => $this->randomName(),
    );
    $this->assertNull($this->paymentType->setConfiguration($configuration));
    $this->assertSame($configuration, $this->paymentType->getConfiguration());
  }

  /**
   * @covers ::setPayment
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($this->paymentType, $this->paymentType->setPayment($payment));
    $this->assertSame($payment, $this->paymentType->getPayment());
  }

  /**
   * @covers ::resumeContext
   *
   * @depends testGetPayment
   */
  public function testResumeContext() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($payment);

    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_TYPE_PRE_RESUME_CONTEXT);

    $this->paymentType->resumeContext();
  }
}
