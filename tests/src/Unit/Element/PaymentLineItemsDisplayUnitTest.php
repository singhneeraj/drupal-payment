<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PaymentLineItemsDisplayUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Element;

use Drupal\payment\Element\PaymentLineItemsDisplay;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Element\PaymentLineItemsDisplay
 *
 * @group Payment
 */
class PaymentLineItemsDisplayUnitTest extends UnitTestCase {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currencyStorage;

  /**
   * The element under test.
   *
   * @var \Drupal\payment\Element\PaymentLineItemsDisplay
   */
  protected $element;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->currencyStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $this->element = new PaymentLineItemsDisplay($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->currencyStorage);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('currency')
      ->willReturn($this->currencyStorage);

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $element = PaymentLineItemsDisplay::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Element\PaymentLineItemsDisplay', $element);
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $info = $this->element->getInfo();
    $this->assertInternalType('array', $info);
    foreach ($info['#pre_render'] as $callback) {
      $this->assertTrue(is_callable($callback));
    }
  }

  /**
   * @covers ::preRender
   */
  public function testPreRender() {
    $line_item_amount = mt_rand();
    $line_item_total_amount = mt_rand();
    $line_item_currency_code = $this->randomMachineName();
    $line_item = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
    $line_item->expects($this->atLeastOnce())
      ->method('getAmount')
      ->willReturn($line_item_amount);
    $line_item->expects($this->atLeastOnce())
      ->method('getCurrencyCode')
      ->willReturn($line_item_currency_code);
    $line_item->expects($this->atLeastOnce())
      ->method('getTotalAmount')
      ->willReturn($line_item_total_amount);

    $payment_currency_code = $this->randomMachineName();
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getCurrencyCode')
      ->willReturn($payment_currency_code);
    $payment->expects($this->atLeastOnce())
      ->method('getLineItems')
      ->willReturn(array($line_item));

    $line_item_currency = $this->getMockBuilder('\Drupal\currency\Entity\Currency')
      ->disableOriginalConstructor()
      ->getMock();
    $line_item_currency->expects($this->exactly(2))
      ->method('formatAmount');

    $payment_currency = $this->getMockBuilder('\Drupal\currency\Entity\Currency')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_currency->expects($this->once())
      ->method('formatAmount');

    $map = array(
      array($line_item_currency_code, $line_item_currency),
      array($payment_currency_code, $payment_currency),
    );
    $this->currencyStorage->expects($this->exactly(2))
      ->method('load')
      ->willReturnMap($map);

    $element = array(
      '#payment' => $payment,
    );

    $build = $this->element->preRender($element);
    $this->assertSame('table', $build['table']['#type']);
  }

  /**
   * @covers ::preRender
   *
   * @expectedException \InvalidArgumentException
   */
  public function testPreRenderWithoutPayment() {
    $element = array();

    $this->element->preRender($element);
  }

}
