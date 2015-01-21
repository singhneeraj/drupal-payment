<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentExecutionResultInterface.
 */

namespace Drupal\payment;

/**
 * Defines a payment execution result.
 *
 * This interface has nothing to do with payment statuses. Instead, it provides
 * information about a payment's execution workflow, during which the payment's
 * status can, but does not have to be changed.
 */
interface PaymentExecutionResultInterface {

  /**
   * Checks whether the execution process has completed.
   *
   * @return bool
   *   Whether the execution process is has completed. When FALSE is
   *   returned, self::getCompletionResponse() MUST return a response.
   */
  public function hasCompleted();

  /**
   * Gets the response to complete payment execution.
   *
   * @return \Drupal\payment\Response\ResponseInterface|null
   *   A response (only if self::isInProgress() returns TRUE) or NULL if payment
   *   execution cannot be completed (anymore).
   */
  public function getCompletionResponse();

}
