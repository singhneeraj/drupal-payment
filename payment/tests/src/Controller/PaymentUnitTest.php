<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Controller\PaymentUnitTest.
 */

namespace Drupal\payment\Tests\Controller;

use Drupal\payment\Controller\Payment;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Controller\Payment
 *
 * @group Payment
 */
class PaymentUnitTest extends UnitTestCase {

  /**
   * The controller under test.
   *
   * @var \Drupal\payment\Controller\Payment
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
    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->controller = new Payment($this->stringTranslation);
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

    $form = Payment::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\Payment', $form);
  }

  /**
   * @covers ::viewTitle
   */
  public function testViewTitle() {
    $id = mt_rand();
    $string = 'Payment #!payment_id';

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('id')
      ->will($this->returnValue($id));

    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->with($string, array(
        '!payment_id' => $id,
      ))
      ->will($this->returnArgument(0));

    $this->assertSame($string, $this->controller->viewTitle($payment));
  }

  /**
   * @covers ::editTitle
   */
  public function testEditTitle() {
    $id = mt_rand();
    $string = 'Edit payment #!payment_id';

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('id')
      ->will($this->returnValue($id));

    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->with($string, array(
        '!payment_id' => $id,
      ))
      ->will($this->returnArgument(0));

    $this->assertSame($string, $this->controller->editTitle($payment));
  }

}
