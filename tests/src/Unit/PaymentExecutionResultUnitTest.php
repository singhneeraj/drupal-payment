<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PaymentExecutionResultUnitTest.
 */

namespace Drupal\Tests\payment\Unit;

use Drupal\Core\Url;
use Drupal\payment\PaymentExecutionResult;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\PaymentExecutionResult
 *
 * @group Payment
 */
class PaymentExecutionResultUnitTest extends UnitTestCase {

  /**
   * The response.
   *
   * @var \Drupal\payment\Response\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $response;

  /**
   * The payment execution result under test.
   *
   * @var \Drupal\payment\PaymentExecutionResult
   */
  protected $paymentExecutionResult;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->response = $this->getMock('\Drupal\payment\Response\ResponseInterface');
  }

  /**
   * @covers ::hasCompleted
   * @covers ::__construct
   */
  function testHasCompleted() {
    $this->paymentExecutionResult = new PaymentExecutionResult();
    $this->assertTrue($this->paymentExecutionResult->hasCompleted());

    $this->paymentExecutionResult = new PaymentExecutionResult($this->response);
    $this->assertFalse($this->paymentExecutionResult->hasCompleted());
  }

  /**
   * @covers ::getCompletionResponse
   * @covers ::__construct
   */
  function testGetCompletionResponse() {
    $this->paymentExecutionResult = new PaymentExecutionResult();
    $this->assertNULL($this->paymentExecutionResult->getCompletionResponse());

    $this->paymentExecutionResult = new PaymentExecutionResult($this->response);
    $this->assertSame($this->response, $this->paymentExecutionResult->getCompletionResponse());
  }

}
