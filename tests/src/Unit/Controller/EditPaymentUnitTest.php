<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\EditPaymentUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\EditPayment;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Controller\EditPayment
 *
 * @group Payment
 */
class EditPaymentUnitTest extends UnitTestCase {

  /**
   * The controller under test.
   *
   * @var \Drupal\payment\Controller\EditPayment
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

    $this->controller = new EditPayment($this->stringTranslation);
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

    $controller = EditPayment::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\EditPayment', $controller);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('id');

    $this->assertInternalType('string', $this->controller->title($payment));
  }

}
