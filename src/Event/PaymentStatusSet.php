<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentStatusSet.
 */

namespace Drupal\payment\Event;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\plugin\payment\status\PaymentStatusInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Provides an event that is dispatched after a new status is set on a payment.
 *
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_STATUS_SET
 */
class PaymentStatusSet extends Event {

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * The previous payment status.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface|null
   */
  protected $previousPaymentStatus;

  /**-
   * Constructs a new class instance.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *   The payment the status was set on.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface $previous_payment_status|null
   *   The payment's previous status.
   */
  public function __construct(PaymentInterface $payment, PaymentStatusInterface $previous_payment_status = NULL) {
    $this->payment = $payment;
    $this->previousPaymentStatus = $previous_payment_status;
  }

  /**
   * Gets the payment the status was set on.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment() {
    return $this->payment;
  }

  /**
   * Gets the payment's previous status.
   *
   * @return \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface|null
   *   The previous status, or NULL of there is none.
   */
  public function getPreviousPaymentStatus() {
    return $this->previousPaymentStatus;
  }

}
