<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\EditPaymentStatusUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\EditPaymentStatus;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\EditPaymentStatus
 *
 * @group Payment
 */
class EditPaymentStatusUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\EditPaymentStatus
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
  protected function setUp() {
    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new EditPaymentStatus($this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = EditPaymentStatus::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\EditPaymentStatus', $form);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $label = $this->randomMachineName();

    $payment_status = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_status->expects($this->once())
      ->method('label')
      ->will($this->returnValue($label));

    $this->assertContains($label, $this->controller->title($payment_status));
  }

}
