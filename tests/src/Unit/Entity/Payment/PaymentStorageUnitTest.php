<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Payment\PaymentStorageUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment;

use Drupal\payment\Entity\Payment\PaymentStorage;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentStorage
 *
 * @group Payment
 */
class PaymentStorageUnitTest extends UnitTestCase {

  /**
   * The entity cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheBackend;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $database;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The payment type manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentTypeManager;

  /**
   * The storage under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStorage
   */
  protected $storage;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->cacheBackend = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->database = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->entityType = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');

    $this->paymentLineItemManager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->paymentStatusManager= $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->paymentTypeManager = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');

    $this->storage = new PaymentStorage($this->entityType, $this->database, $this->entityManager, $this->cacheBackend, $this->paymentLineItemManager, $this->paymentMethodManager, $this->paymentStatusManager, $this->paymentTypeManager);
  }

  /**
   * @covers ::createInstance
   */
  public function testCreateInstance() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('cache.entity', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->cacheBackend),
      array('database', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->database),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager),
      array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('plugin.manager.payment.type', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentTypeManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $storage = PaymentStorage::createInstance($container, $this->entityType);
    $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentStorage', $storage);
  }

  /**
   * @covers ::mapToStorageRecord
   */
  public function testMapToStorageRecord() {
    $bundle = $this->randomMachineName();
    $currency_code = $this->randomMachineName();
    $id = $this->randomMachineName();
    $first_payment_status_id = mt_rand();
    $last_payment_status_id = mt_rand();
    $owner_id = mt_rand();
    $uuid = $this->randomMachineName();
    $payment_method_id = $this->randomMachineName();
    $payment_method_configuration = array(
      'foo' => $this->randomMachineName(),
    );
    $payment_type_id = $this->randomMachineName();
    $payment_type_configuration = array(
      'foo' => $this->randomMachineName(),
    );

    $first_payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $first_payment_status->expects($this->any())
      ->method('getId')
      ->will($this->returnValue($first_payment_status_id));
    $last_payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $last_payment_status->expects($this->any())
      ->method('getId')
      ->will($this->returnValue($last_payment_status_id));

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->will($this->returnValue($payment_method_id));
    $payment_method->expects($this->atLeastOnce())
      ->method('getConfiguration')
      ->will($this->returnValue($payment_method_configuration));

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $payment_type->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->will($this->returnValue($payment_type_id));
    $payment_type->expects($this->atLeastOnce())
      ->method('getConfiguration')
      ->will($this->returnValue($payment_type_configuration));

    $payment= $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('bundle')
      ->will($this->returnValue($bundle));
    $payment->expects($this->atLeastOnce())
      ->method('getCurrencyCode')
      ->will($this->returnValue($currency_code));
    $payment->expects($this->atLeastOnce())
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id));
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $payment->expects($this->once())
      ->method('getPaymentStatus')
      ->will($this->returnValue($last_payment_status));
    $payment->expects($this->once())
      ->method('getPaymentStatuses')
      ->will($this->returnValue(array($first_payment_status, $last_payment_status)));
    $payment->expects($this->atLeastOnce())
      ->method('id')
      ->will($this->returnValue($id));
    $payment->expects($this->atLeastOnce())
      ->method('uuid')
      ->will($this->returnValue($uuid));

    $method = new \ReflectionMethod($this->storage, 'mapToStorageRecord');
    $method->setAccessible(TRUE);

    $record = $method->invoke($this->storage, $payment);
    $this->assertInstanceOf('\stdClass', $record);
    $this->assertSame($uuid, $record->uuid);
    $this->assertSame($id, $record->id);
    $this->assertSame($currency_code, $record->currency);
    $this->assertSame($bundle, $record->bundle);
    $this->assertSame($owner_id, $record->owner);
    $this->assertSame($first_payment_status_id, $record->first_payment_status_id);
    $this->assertSame($last_payment_status_id, $record->last_payment_status_id);
    $this->assertSame($payment_method_id, $record->payment_method_id);
    $this->assertSame(serialize($payment_method_configuration), $record->payment_method_configuration);
    $this->assertSame($payment_type_id, $record->payment_type_id);
    $this->assertSame(serialize($payment_type_configuration), $record->payment_type_configuration);
  }

}
