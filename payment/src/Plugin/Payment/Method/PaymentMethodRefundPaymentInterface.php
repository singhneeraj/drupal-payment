<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\PaymentMethodRefundPaymentInterface.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Core\Session\AccountInterface;

/**
 * Defines a payment method that can Refund authorized payments.
 *
 * Users can refund payments if they have the "payment.payment.refund.any"
 * permissions and self::refundPaymentAccess() returns TRUE.
 */
interface PaymentMethodRefundPaymentInterface {

  /**
   * Checks if the payment can be refunded.
   *
   * The payment method must have been configured and the payment must have been
   * captured prior to refunding it.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   *
   * @see self::refundPayment
   */
  public function refundPaymentAccess(AccountInterface $account);

  /**
   * Refunds the payment.
   *
   * Implementations must invoke hook_payment_pre_refund() and dispatch the
   * \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_REFUND Symfony event
   * before refunding the payment.
   *
   * @see self::refundPaymentAccess
   */
  public function refundPayment();

}
