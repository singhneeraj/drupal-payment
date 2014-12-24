<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\ViewPaymentUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\ViewPayment;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Controller\ViewPayment
 *
 * @group Payment
 */
class ViewPaymentUnitTest extends UnitTestCase {

  /**
   * The controller under test.
   *
   * @var \Drupal\payment\Controller\ViewPayment
   */
  protected $controller;

  /**
   * The string translator.
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
    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new ViewPayment($this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $container->expects($this->once())
      ->method('get')
      ->with('string_translation')
      ->will($this->returnValue($this->stringTranslation));

    $controller = ViewPayment::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\ViewPayment', $controller);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('id');

    $this->assertInternalType('string', $this->controller->title($payment));
  }

}
