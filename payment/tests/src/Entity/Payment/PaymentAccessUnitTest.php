<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Payment\PaymentAccessUnitTest.
 */

namespace Drupal\payment\Tests\Entity\Payment;

use Drupal\payment\Entity\Payment\PaymentAccess;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodCapturePaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodRefundPaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodUpdatePaymentStatusInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentAccess
 *
 * @group Payment
 */
class PaymentAccessUnitTest extends UnitTestCase {

  /**
   * The access controller under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentAccess
   */
  protected $accessController;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->accessController = new PaymentAccess($entity_type);
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccessCapture
   */
  public function testCheckAccessCapture($expected, $payment_method_interface, $payment_method_capture_access, $has_permissions) {
    $operation = 'capture';
    $language_code = $this->randomName();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $map = array(
      array('payment.payment.capture.any', $has_permissions),
      array('payment.payment.capture.own', $has_permissions),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));

    $payment_method = $this->getMock($payment_method_interface);
    $payment_method->expects($this->any())
      ->method('capturePaymentAccess')
      ->with($account)
      ->will($this->returnValue($payment_method_capture_access));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));

    $method = new \ReflectionMethod($this->accessController, 'checkAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected, $method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * Provides data to self::testCheckAccessCapture().
   */
  public function providerTestCheckAccessCapture() {
    return array(
      array(TRUE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodCapturePaymentInterface', TRUE, TRUE),
      array(FALSE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodCapturePaymentInterface', FALSE, TRUE),
      array(FALSE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodCapturePaymentInterface', TRUE, FALSE),
      array(FALSE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodCapturePaymentInterface', FALSE, FALSE),
      array(FALSE, '\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface', TRUE, TRUE),
    );
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccessRefund
   */
  public function testCheckAccessRefund($expected, $payment_method_interface, $payment_method_refund_access, $has_permissions) {
    $operation = 'refund';
    $language_code = $this->randomName();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $map = array(
      array('payment.payment.refund.any', $has_permissions),
      array('payment.payment.refund.own', $has_permissions),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));

    $payment_method = $this->getMock($payment_method_interface);
    $payment_method->expects($this->any())
      ->method('refundPaymentAccess')
      ->with($account)
      ->will($this->returnValue($payment_method_refund_access));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));

    $method = new \ReflectionMethod($this->accessController, 'checkAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected, $method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * Provides data to self::testCheckAccessRefund().
   */
  public function providerTestCheckAccessRefund() {
    return array(
      array(TRUE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodRefundPaymentInterface', TRUE, TRUE),
      array(FALSE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodRefundPaymentInterface', FALSE, TRUE),
      array(FALSE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodRefundPaymentInterface', TRUE, FALSE),
      array(FALSE, '\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodRefundPaymentInterface', FALSE, FALSE),
      array(FALSE, '\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface', TRUE, TRUE),
    );
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessUpdateStatusWithAccess() {
    $operation = 'update_status';
    $language_code = $this->randomName();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('payment.payment.update_status.any')
      ->will($this->returnValue(TRUE));

    $payment_method = $this->getMock('\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodUpdateStatusInterface');
    $payment_method->expects($this->once())
      ->method('updatePaymentStatusAccess')
      ->with($account)
      ->will($this->returnValue(TRUE));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTRUE($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessUpdateStatusWithoutAccess() {
    $operation = 'update_status';
    $language_code = $this->randomName();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->never())
      ->method('hasPermission');

    $payment_method = $this->getMock('\Drupal\payment\Tests\Entity\Payment\PaymentAccessUnitTestDummyPaymentMethodUpdateStatusInterface');
    $payment_method->expects($this->once())
      ->method('updatePaymentStatusAccess')
      ->with($account)
      ->will($this->returnValue(FALSE));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertFALSE($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   * @covers ::checkAccessPermission
   */
  public function testCheckAccessWithoutPermission() {
    $operation = $this->randomName();
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   * @covers ::checkAccessPermission
   */
  public function testCheckAccessWithAnyPermission() {
    $operation = $this->randomName();
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('payment.payment.' . $operation . '.any')
      ->will($this->returnValue(TRUE));
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   * @covers ::checkAccessPermission
   */
  public function testCheckAccessWithOwnPermission() {
    $owner_id = mt_rand();
    $operation = $this->randomName();
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('id')
      ->will($this->returnValue($owner_id));
    $map = array(
      array('payment.payment.' . $operation . '.any', FALSE),
      array('payment.payment.' . $operation . '.own', TRUE),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->at(0))
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id));
    $payment->expects($this->at(1))
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id + 1));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkCreateAccess
   */
  public function testCheckCreateAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $context = array();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkCreateAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($account, $context)));
  }

  /**
   * @covers ::getCache
   */
  public function testGetCache() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $cache_id = $this->randomName();
    $operation = $this->randomName();
    $language_code = $this->randomName();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('getCache');
    $method->setAccessible(TRUE);
    $this->assertNull($method->invokeArgs($this->accessController, array($cache_id, $operation, $language_code, $account)));
  }

}

/**
 * Extends two interfaces, because we can only mock one.
 */
interface PaymentAccessUnitTestDummyPaymentMethodUpdateStatusInterface extends PaymentMethodUpdatePaymentStatusInterface, PaymentMethodInterface {
}

/**
 * Extends two interfaces, because we can only mock one.
 */
interface PaymentAccessUnitTestDummyPaymentMethodCapturePaymentInterface extends PaymentMethodCapturePaymentInterface, PaymentMethodInterface {
}

/**
 * Extends two interfaces, because we can only mock one.
 */
interface PaymentAccessUnitTestDummyPaymentMethodRefundPaymentInterface extends PaymentMethodRefundPaymentInterface, PaymentMethodInterface {
}
