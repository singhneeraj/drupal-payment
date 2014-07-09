<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentEvents.
 */

namespace Drupal\payment\Event;

/**
 * Defines Payment events.
 */
final class PaymentEvents {

  /**
   * The name of the event that is fired when payment execution access is
   * checked.
   *
   * @see hook_payment_execute_access()
   * @see \Drupal\payment\Event\PaymentExecuteAccess
   */
  const PAYMENT_EXECUTE_ACCESS = 'drupal.payment.payment_execute_access';

  /**
   * The name of the event that is fired before a payment is executed.
   *
   * @see hook_payment_pre_execute()
   * @see \Drupal\payment\Event\PaymentPreExecute
   */
  const PAYMENT_PRE_EXECUTE = 'drupal.payment.payment_pre_execute';

  /**
   * The name of the event that is fired before a payment is captured.
   *
   * @see hook_payment_pre_capture()
   * @see \Drupal\payment\Event\PaymentPreCapture
   */
  const PAYMENT_PRE_CAPTURE = 'drupal.payment.payment_pre_capture';

  /**
   * The name of the event that is fired before a payment is refunded.
   *
   * @see hook_payment_pre_refunded()
   * @see \Drupal\payment\Event\PaymentPreRefund
   */
  const PAYMENT_PRE_REFUND = 'drupal.payment.payment_pre_refund';

  /**
   * The name of the event that is fired after a new payment status is set.
   *
   * @see hook_payment_status_set()
   * @see \Drupal\payment\Event\PaymentStatusSet
   */
  const PAYMENT_STATUS_SET = 'drupal.payment.payment_status_set';

  /**
   * The name of the event that is fired before the payment type's original
   * context is resumed.
   *
   * @see hook_payment_type_pre_resume_context()
   * @see \Drupal\payment\Event\PaymentTypePreResumeContext
   */
  const PAYMENT_TYPE_PRE_RESUME_CONTEXT = 'drupal.payment.payment_type_pre_resume_context';

  /**
   * The name of the event that alters
   * \Drupal\payment\QueueInterface::loadPaymentIds() results.
   *
   * @see hook_payment_queue_payment_ids_alter()
   * @see \Drupal\payment\Event\PaymentQueuePaymentIdsAlter
   */
  const PAYMENT_QUEUE_PAYMENT_IDS_ALTER = 'drupal.payment.payment_queue_payment_ids_alter';
}
