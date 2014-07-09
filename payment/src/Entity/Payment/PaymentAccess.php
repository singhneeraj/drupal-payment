<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\Payment\PaymentAccess.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodCapturePaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodRefundPaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodUpdatePaymentStatusInterface;

/**
 * Defines the default list controller for ConfigEntity objects.
 */
class PaymentAccess extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $payment, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */

    if ($operation == 'update_status') {
      $payment_method = $payment->getPaymentMethod();
      if ($payment_method instanceof PaymentMethodUpdatePaymentStatusInterface && !$payment_method->updatePaymentStatusAccess($account)) {
        return FALSE;
      }
    }
    elseif ($operation == 'capture') {
      $payment_method = $payment->getPaymentMethod();
      return $payment_method instanceof PaymentMethodCapturePaymentInterface
      && $payment_method->capturePaymentAccess($account)
      && $this->checkAccessPermission($payment, $operation, $account);
    }
    elseif ($operation == 'refund') {
      $payment_method = $payment->getPaymentMethod();
      return $payment_method instanceof PaymentMethodRefundPaymentInterface
      && $payment_method->refundPaymentAccess($account)
      && $this->checkAccessPermission($payment, $operation, $account);
    }
    return $this->checkAccessPermission($payment, $operation, $account);
  }

  /**
   * Checks if a user has permission to perform a payment operation.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   * @param string $operation
   * @param \Drupal\Core\Session\AccountInterface
   *
   * @return bool
   */
  protected function checkAccessPermission(PaymentInterface $payment, $operation, AccountInterface $account) {
    return $account->hasPermission('payment.payment.' . $operation . '.any') || $account->hasPermission('payment.payment.' . $operation . '.own') && $account->id() == $payment->getOwnerId();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // We let other modules decide whether users have access to create
    // new payments. There is no corresponding permission for this operation.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCache($cid, $operation, $langcode, AccountInterface $account) {
    // Disable the cache, because the intensive operations are cached elsewhere
    // already and the results of all other operations are too volatile to be
    // cached.
  }
}
