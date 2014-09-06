<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Method\BasicUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\Basic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\Basic
 *
 * @group Payment
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
   *
   * @covers ::__construct
   */
  public function setUp() {
    parent::setUp();

    $this->pluginDefinition += array(
      'entity_id' => $this->randomMachineName(),
      'execute_status_id' => $this->randomMachineName(),
      'capture' => TRUE,
      'capture_status_id' => $this->randomMachineName(),
      'refund' => TRUE,
      'refund_status_id' => $this->randomMachineName(),
    );

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->paymentMethod = new Basic(array(), '', $this->pluginDefinition, $this->eventDispatcher, $this->token, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
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
   * @covers ::getExecuteStatusId
   */
  public function testGetExecuteStatusId() {
    $this->assertSame($this->pluginDefinition['execute_status_id'], $this->paymentMethod->getExecuteStatusId());
  }

  /**
   * @covers ::getCaptureStatusId
   */
  public function testGetCaptureStatusId() {
    $this->assertSame($this->pluginDefinition['capture_status_id'], $this->paymentMethod->getCaptureStatusId());
  }

  /**
   * @covers ::getCapture
   */
  public function testGetCapture() {
    $this->assertSame($this->pluginDefinition['capture'], $this->paymentMethod->getCapture());
  }

  /**
   * @covers ::getRefundStatusId
   */
  public function testGetRefundStatusId() {
    $this->assertSame($this->pluginDefinition['refund_status_id'], $this->paymentMethod->getRefundStatusId());
  }

  /**
   * @covers ::getRefund
   */
  public function testGetRefund() {
    $this->assertSame($this->pluginDefinition['refund'], $this->paymentMethod->getRefund());
  }

  /**
   * @covers ::doExecutePayment
   */
  public function testDoExecutePayment() {
    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($this->pluginDefinition['execute_status_id'])
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
      ->method('setPaymentStatus')
      ->with($payment_status);

    $this->paymentMethod->setPayment($payment);

    $method = new \ReflectionMethod($this->paymentMethod, 'doExecutePayment');
    $method->setAccessible(TRUE);

    $method->invoke($this->paymentMethod, $payment);
  }

  /**
   * @covers ::doCapturePayment
   */
  public function testDoCapturePayment() {
    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($this->pluginDefinition['capture_status_id'])
      ->will($this->returnValue($payment_status));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('save');
    $payment->expects($this->once())
      ->method('setPaymentStatus')
      ->with($payment_status);

    $this->paymentMethod->setPayment($payment);

    $method = new \ReflectionMethod($this->paymentMethod, 'doCapturePayment');
    $method->setAccessible(TRUE);

    $method->invoke($this->paymentMethod, $payment);
  }

  /**
   * @covers ::doCapturePaymentAccess
   *
   * @dataProvider providerDoCapturePaymentAccess
   */
  public function testDoCapturePaymentAccess($expected, $capture, $current_status_id, $execute_status_id, $capture_status_id) {
    $this->pluginDefinition['execute_status_id'] = $execute_status_id;
    $this->pluginDefinition['capture'] = $capture;
    $this->pluginDefinition['capture_status_id'] = $capture_status_id;

    $this->paymentMethod = new Basic(array(), '', $this->pluginDefinition, $this->eventDispatcher, $this->token, $this->paymentStatusManager);

    $capture_payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $capture_payment_status->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($current_status_id));

    $capture_payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $capture_payment_status->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($current_status_id));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->any())
      ->method('getPaymentStatus')
      ->will($this->returnValue($capture_payment_status));

    $this->paymentMethod->setPayment($payment);

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $method = new \ReflectionMethod($this->paymentMethod, 'doCapturePaymentAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected, $method->invoke($this->paymentMethod, $account));
  }

  /**
   * Provides data to self::testDoCapturePaymentAccess().
   */
  public function providerDoCapturePaymentAccess() {
    $status_id_a = $this->randomMachineName();
    $status_id_b = $this->randomMachineName();
    $status_id_c = $this->randomMachineName();
    return array(
      array(TRUE, TRUE, $status_id_a, $status_id_a, $status_id_b),
      array(FALSE, TRUE, $status_id_a, $status_id_b, $status_id_c),
      array(FALSE, FALSE, $status_id_a, $status_id_a, $status_id_b),
    );
  }

  /**
   * @covers ::doRefundPayment
   */
  public function testDoRefundPayment() {
    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($this->pluginDefinition['refund_status_id'])
      ->will($this->returnValue($payment_status));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('save');
    $payment->expects($this->once())
      ->method('setPaymentStatus')
      ->with($payment_status);

    $this->paymentMethod->setPayment($payment);

    $method = new \ReflectionMethod($this->paymentMethod, 'doRefundPayment');
    $method->setAccessible(TRUE);

    $method->invoke($this->paymentMethod, $payment);
  }

  /**
   * @covers ::doRefundPaymentAccess
   *
   * @dataProvider providerDoRefundPaymentAccess
   */
  public function testDoRefundPaymentAccess($expected, $refund, $current_status_id, $capture_status_id, $refund_status_id) {
    $this->pluginDefinition['capture_status_id'] = $capture_status_id;
    $this->pluginDefinition['refund'] = $refund;
    $this->pluginDefinition['refund_status_id'] = $refund_status_id;

    $this->paymentMethod = new Basic(array(), '', $this->pluginDefinition, $this->eventDispatcher, $this->token, $this->paymentStatusManager);

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $payment_status->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($current_status_id));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->any())
      ->method('getPaymentStatus')
      ->will($this->returnValue($payment_status));

    $this->paymentMethod->setPayment($payment);

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $method = new \ReflectionMethod($this->paymentMethod, 'doRefundPaymentAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected, $method->invoke($this->paymentMethod, $account));
  }

  /**
   * Provides data to self::testDoRefundPaymentAccess().
   */
  public function providerDoRefundPaymentAccess() {
    $status_id_a = $this->randomMachineName();
    $status_id_b = $this->randomMachineName();
    $status_id_c = $this->randomMachineName();
    return array(
      array(TRUE, TRUE, $status_id_a, $status_id_a, $status_id_b),
      array(FALSE, TRUE, $status_id_a, $status_id_b, $status_id_c),
      array(FALSE, FALSE, $status_id_a, $status_id_a, $status_id_b),
    );
  }

  /**
   * @covers ::updatePaymentStatusAccess
   */
  public function testUpdatePaymentStatusAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertFalse($this->paymentMethod->updatePaymentStatusAccess($account));
  }

  /**
   * @covers ::getSettablePaymentStatuses
   */
  public function testGetSettablePaymentStatuses() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertSame(array(), $this->paymentMethod->getSettablePaymentStatuses($account, $payment));
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
