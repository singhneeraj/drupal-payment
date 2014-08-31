<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\PaymentStatusesDisplayUnitTest.
 */

namespace Drupal\payment\Tests\Element;

use Drupal\payment\Element\PaymentStatusesDisplay;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Element\PaymentStatusesDisplay
 *
 * @group Payment
 */
class PaymentStatusesDisplayUnitTest extends UnitTestCase {

  /**
   * The fate formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $dateFormatter;

  /**
   * The element under test.
   *
   * @var \Drupal\payment\Element\PaymentStatusesDisplay
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
    $this->dateFormatter = $this->getMockBuilder('\Drupal\Core\Datetime\DateFormatter')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $this->element = new PaymentStatusesDisplay($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->dateFormatter);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('date.formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->dateFormatter),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $element = PaymentStatusesDisplay::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Element\PaymentStatusesDisplay', $element);
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
    $payment_status_created = mt_rand();
    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $payment_status->expects($this->atLeastOnce())
      ->method('getCreated')
      ->willReturn($payment_status_created);

    $this->dateFormatter->expects($this->once())
      ->method('format')
      ->with($payment_status_created);

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentStatuses')
      ->willReturn(array($payment_status));

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
