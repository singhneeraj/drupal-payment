<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentExecutionResult.
 */

namespace Drupal\payment;

use Drupal\payment\Response\ResponseInterface;

/**
 * Provides a payment execution result.
 */
class PaymentExecutionResult implements PaymentExecutionResultInterface {

  /**
   * The response.
   *
   * @var \Drupal\payment\Response\ResponseInterface|null
   */
  protected $response;

  /**
   * Creates a new instance.
   *
   * @param \Drupal\payment\Response\ResponseInterface|null $response
   *   A response or NULL if execution has completed.
   */
  public function __construct(ResponseInterface $response = NULL) {
    $this->response = $response;
  }

  /**
   * {@inheritdoc}
   */
  public function hasCompleted() {
    return is_null($this->response);
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletionResponse() {
    return $this->response;
  }

}
