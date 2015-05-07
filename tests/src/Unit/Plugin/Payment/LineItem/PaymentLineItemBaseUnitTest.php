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
   * {@inheritdoc}
   */
  public function setUp() {
    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->lineItem = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      $this->randomMachineName() => mt_rand(),
    ) + $this->lineItem->defaultConfiguration();
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
    $total_amount = '49';

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    /** @var \Drupal\payment\Plugin\Payment\LineItem\Basic|\PHPUnit_Framework_MockObject_MockObject $line_item */
    $line_item = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase')
      ->setMethods(array('formElements', 'getAmount', 'getConfigurationFromFormValues', 'getCurrencyCode', 'getDescription', 'getQuantity'))
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition))
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
    $this->assertSame([], $this->lineItem->calculateDependencies());
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
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->assertSame([], $this->lineItem->buildConfigurationForm($form, $form_state));
  }

  /**
   * @covers ::validateConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->lineItem->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->lineItem->submitConfigurationForm($form, $form_state);
  }

}
