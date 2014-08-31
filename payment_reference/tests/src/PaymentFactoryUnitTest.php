<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\PaymentFactoryUnitTest.
 */

namespace Drupal\payment_reference\Tests;

use Drupal\payment_reference\PaymentFactory;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_reference\PaymentFactory
 *
 * @group Payment Reference Field
 */
class PaymentFactoryUnitTest extends UnitTestCase {

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment line item manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The factory under test.
   *
   * @var \Drupal\payment_reference\PaymentFactory
   */
  protected $factory;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->paymentLineItemManager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');

    $this->factory = new PaymentFactory($this->entityManager, $this->paymentLineItemManager);
  }

  /**
   * @covers ::createPayment
   */
  public function testCreatePayment() {
    $bundle = $this->randomMachineName();
    $currency_code = $this->randomMachineName();
    $entity_type_id = $this->randomMachineName();
    $field_name = $this->randomMachineName();

    $payment_type = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('setBundle')
      ->with($bundle);
    $payment_type->expects($this->once())
      ->method('setEntityTypeId')
      ->with($entity_type_id);
    $payment_type->expects($this->once())
      ->method('setFieldName')
      ->with($field_name);

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('setCurrencyCode')
      ->with($currency_code);
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->willReturn($payment_type);

    $payment_storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $payment_storage->expects($this->once())
      ->method('create')
      ->with(array(
        'bundle' => 'payment_reference',
      ))
      ->willReturn($payment);

    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with('payment')
      ->willReturn($payment_storage);

    $line_item_a = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
    $line_item_plugin_id_a = $this->randomMachineName();
    $line_item_plugin_configuration_a = array(
      'foo' => $this->randomMachineName(),
    );
    $line_item_b = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
    $line_item_plugin_id_b = $this->randomMachineName();
    $line_item_plugin_configuration_b = array(
      'bar' => $this->randomMachineName(),
    );

    $field_storage_definition = $this->getMock('\Drupal\Core\Field\FieldStorageDefinitionInterface');
    $field_storage_definition->expects($this->once())
      ->method('getTargetEntityTypeId')
      ->willReturn($entity_type_id);

    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->once())
      ->method('getBundle')
      ->willReturn($bundle);
    $field_definition->expects($this->once())
      ->method('getFieldStorageDefinition')
      ->willReturn($field_storage_definition);
    $field_definition->expects($this->once())
      ->method('getName')
      ->willreturn($field_name);
    $map = array(
      array('currency_code', $currency_code),
      array('line_items_data', array(
        array(
          'plugin_configuration' => $line_item_plugin_configuration_a,
          'plugin_id' => $line_item_plugin_id_a,
        ),
        array(
          'plugin_configuration' => $line_item_plugin_configuration_b,
          'plugin_id' => $line_item_plugin_id_b,
        ),
      )),
    );
    $field_definition->expects($this->exactly(2))
      ->method('getSetting')
      ->willReturnMap($map);

    $this->paymentLineItemManager->expects($this->at(0))
      ->method('createInstance')
      ->with($line_item_plugin_id_a, $line_item_plugin_configuration_a)
      ->willReturn($line_item_a);
    $this->paymentLineItemManager->expects($this->at(1))
      ->method('createInstance')
      ->with($line_item_plugin_id_b, $line_item_plugin_configuration_b)
      ->willReturn($line_item_b);

    $this->assertSame($payment, $this->factory->createPayment($field_definition));
  }
}
