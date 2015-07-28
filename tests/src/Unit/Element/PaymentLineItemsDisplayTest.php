<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PaymentLineItemsDisplayTest.
 */

namespace Drupal\Tests\payment\Unit\Element;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\payment\Element\PaymentLineItemsDisplay;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Element\PaymentLineItemsDisplay
 *
 * @group Payment
 */
class PaymentLineItemsDisplayTest extends UnitTestCase {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currencyStorage;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Element\PaymentLineItemsDisplay
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->currencyStorage = $this->getMock(EntityStorageInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->sut = new PaymentLineItemsDisplay($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->currencyStorage);
  }

  /**
   * @covers ::__construct
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock(EntityManagerInterface::class);
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('currency')
      ->willReturn($this->currencyStorage);

    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $sut = PaymentLineItemsDisplay::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(PaymentLineItemsDisplay::class, $sut);
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $info = $this->sut->getInfo();
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
    $line_item = $this->getMock(PaymentLineItemInterface::class);
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
    $payment = $this->getMock(PaymentInterface::class);
    $payment->expects($this->atLeastOnce())
      ->method('getCurrencyCode')
      ->willReturn($payment_currency_code);

    $line_item_currency = $this->getMock(CurrencyInterface::class);
    $line_item_currency->expects($this->exactly(2))
      ->method('formatAmount');

    $payment_currency = $this->getMock(CurrencyInterface::class);
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
      '#payment_line_items' => [$line_item],
    );

    $build = $this->sut->preRender($element);
    $this->assertSame('table', $build['table']['#type']);
  }

  /**
   * @covers ::preRender
   *
   * @expectedException \InvalidArgumentException
   */
  public function testPreRenderWithoutPaymentLineItems() {
    $element = [
      '#payment' => $this->getMock(PaymentInterface::class),
    ];

    $this->sut->preRender($element);
  }

}
