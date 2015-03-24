<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Status\ConfigOperationsProviderUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Status;

use Drupal\payment\Plugin\Payment\Status\ConfigOperationsProvider;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Status\ConfigOperationsProvider
 *
 * @group Payment
 */
class ConfigOperationsProviderUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\ConfigOperationsProvider
   */
  protected $operationsProvider;

  /**
   * The payment status list builder.
   *
   * @var \Drupal\Core\Entity\EntityListBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusListBuilder;

  /**
   * The payment status storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentStatusListBuilder = $this->getMock('\Drupal\Core\Entity\EntityListBuilderInterface');

    $this->paymentStatusStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->operationsProvider = new ConfigOperationsProvider($this->paymentStatusStorage, $this->paymentStatusListBuilder);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  public function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment_status')
      ->will($this->returnValue($this->paymentStatusStorage));
    $entity_manager->expects($this->once())
      ->method('getListBuilder')
      ->with('payment_status')
      ->will($this->returnValue($this->paymentStatusListBuilder));

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $container->expects($this->once())
      ->method('get')
      ->with('entity.manager')
      ->will($this->returnValue($entity_manager));

    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\Status\ConfigOperationsProvider', ConfigOperationsProvider::create($container));
  }

    /**
     * @covers ::getOperations
     */
    public function testGetOperations() {
    $entity_id = $this->randomMachineName();
    $plugin_id = 'payment_config:' . $entity_id;

    $payment_status = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusStorage->expects($this->once())
      ->method('load')
      ->with($entity_id)
      ->will($this->returnValue($payment_status));

    $operations = array(
      'foo' => array(
        'title' => $this->randomMachineName(),
      ),
    );
    $this->paymentStatusListBuilder->expects($this->once())
      ->method('getOperations')
      ->with($payment_status)
      ->will($this->returnValue($operations));

    $this->assertSame($operations, $this->operationsProvider->getOperations($plugin_id));
  }

}
