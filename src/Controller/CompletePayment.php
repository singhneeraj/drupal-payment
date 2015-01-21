<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\CompletePayment.
 */

namespace Drupal\payment\Controller;

use Drupal\payment\Entity\PaymentInterface;

/**
 * Handles the "complete payment" route.
 */
class CompletePayment {

  /**
   * Completes a payment.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function execute(PaymentInterface $payment) {
    return $payment->getPaymentMethod()->getPaymentExecutionResult()->getCompletionResponse()->getResponse();
  }

}
