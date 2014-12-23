<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\LineItem\PaymentLineItemBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\LineItem;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase
 *
 * @group Payment
 */
class PaymentLineItemBaseUnitTest extends UnitTestCase {

  /**
   * The line item under test.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\Basic|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $lineItem;

  /**
   * The math service used for testing.
   *
   * @var \Drupal\currency\Math\MathInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $math;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->math = $this->getMock('\Drupal\currency\Math\MathInterface');

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $this->lineItem = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->math))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   */
  public function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('currency.math', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->math),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase $class_name */
    $class_name = get_class($this->lineItem);

    $line_item = $class_name::create($container, array(), $this->randomMachineName(), array());
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase', $line_item);
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      $this->randomMachineName() => mt_rand(),
    );
    $return = $this->lineItem->setConfiguration($configuration);
    $this->assertNull($return);
    $this->assertSame($configuration, $this->lineItem->getConfiguration());
  }

  /**
   * @covers ::setQuantity
   * @covers ::getQuantity
   */
  public function testGetQuantity() {
    $quantity = 7.7;
    $this->assertSame($this->lineItem, $this->lineItem->setQuantity($quantity));
    $this->assertSame($quantity, $this->lineItem->getQuantity());
  }

  /**
   * @covers ::getTotalAmount
   */
  public function testGetTotalAmount() {
    $amount= 7;
    $quantity = 7;
    $total_amount = 49;

    $this->math->expects($this->once())
      ->method('multiply')
      ->with($amount, $quantity)
      ->will($this->returnValue($total_amount));

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    /** @var \Drupal\payment\Plugin\Payment\LineItem\Basic|\PHPUnit_Framework_MockObject_MockObject $line_item */
    $line_item = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase')
      ->setMethods(array('formElements', 'getAmount', 'getConfigurationFromFormValues', 'getCurrencyCode', 'getDescription', 'getQuantity'))
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->math))
      ->getMock();
    $line_item->expects($this->once())
      ->method('getAmount')
      ->will($this->returnValue($amount));
    $line_item->expects($this->once())
      ->method('getQuantity')
      ->will($this->returnValue($quantity));

    $this->assertSame($total_amount, $line_item->getTotalAmount());
  }

  /**
   * @covers ::setName
   * @covers ::getName
   */
  public function testGetName() {
    $name = $this->randomMachineName();
    $this->assertSame($this->lineItem, $this->lineItem->setName($name));
    $this->assertSame($name, $this->lineItem->getName());
  }

  /**
   * @covers ::setPayment
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($this->lineItem, $this->lineItem->setPayment($payment));
    $this->assertSame($payment, $this->lineItem->getPayment());
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame(array(), $this->lineItem->calculateDependencies());
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $default_configuration = array(
      'name' => NULL,
      'quantity' => 1,
    );

    $this->assertSame($default_configuration, $this->lineItem->defaultConfiguration());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->assertSame(array(), $this->lineItem->buildConfigurationForm($form, $form_state));
  }

  /**
   * @covers ::validateConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->lineItem->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->lineItem->submitConfigurationForm($form, $form_state);
  }

}
