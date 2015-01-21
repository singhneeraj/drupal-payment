<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\ViewPaymentUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\CompletePayment;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Controller\CompletePayment
 *
 * @group Payment
 */
class CompletePaymentUnitTest extends UnitTestCase {

  /**
   * The controller under test.
   *
   * @var \Drupal\payment\Controller\CompletePayment
   */
  protected $controller;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->controller = new CompletePayment();
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $response = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
      ->disableOriginalConstructor()
      ->getMock();

    $completion_response = $this->getMock('\Drupal\payment\Response\ResponseInterface');
    $completion_response->expects($this->atLeastOnce())
      ->method('getResponse')
      ->willReturn($response);

    $execution_result = $this->getMock('\Drupal\payment\PaymentExecutionResultInterface');
    $execution_result->expects($this->atLeastOnce())
      ->method('getCompletionResponse')
      ->willReturn($completion_response);

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->atLeastOnce())
      ->method('getPaymentExecutionResult')
      ->willReturn($execution_result);

    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');
    $payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->willReturn($payment_method);

    $this->assertSame($response, $this->controller->execute($payment));
  }

}
