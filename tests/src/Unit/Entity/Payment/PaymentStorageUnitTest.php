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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

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

    $this->languageManager = $this->getMock('\Drupal\Core\Language\LanguageManagerInterface');

    $this->paymentStatusManager= $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->paymentTypeManager = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');

    $this->storage = new PaymentStorage($this->entityType, $this->database, $this->entityManager, $this->cacheBackend, $this->languageManager, $this->paymentStatusManager, $this->paymentTypeManager);
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
      array('language_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->languageManager),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('plugin.manager.payment.type', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentTypeManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $storage = PaymentStorage::createInstance($container, $this->entityType);
    $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentStorage', $storage);
  }

}
