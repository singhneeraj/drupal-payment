<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PaymentExecutionResultTest.
 */

namespace Drupal\Tests\payment\Unit;

use Drupal\payment\PaymentExecutionResult;
use Drupal\payment\Response\ResponseInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\PaymentExecutionResult
 *
 * @group Payment
 */
class PaymentExecutionResultTest extends UnitTestCase {

  /**
   * The response.
   *
   * @var \Drupal\payment\Response\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $response;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\PaymentExecutionResult
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->response = $this->getMock(ResponseInterface::class);
  }

  /**
   * @covers ::hasCompleted
   * @covers ::__construct
   */
  function testHasCompleted() {
    $this->sut = new PaymentExecutionResult();
    $this->assertTrue($this->sut->hasCompleted());

    $this->sut = new PaymentExecutionResult($this->response);
    $this->assertFalse($this->sut->hasCompleted());
  }

  /**
   * @covers ::getCompletionResponse
   * @covers ::__construct
   */
  function testGetCompletionResponse() {
    $this->sut = new PaymentExecutionResult();
    $this->assertNULL($this->sut->getCompletionResponse());

    $this->sut = new PaymentExecutionResult($this->response);
    $this->assertSame($this->response, $this->sut->getCompletionResponse());
  }

}
