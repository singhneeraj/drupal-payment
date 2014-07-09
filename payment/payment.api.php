<?php

/**
 * @file
 * Hook documentation.
 */

use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface;

/**
 * Alters payment status plugins.
 *
 * @param array $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_payment_status_alter(array &$definitions) {
  // Rename a plugin.
  $definitions['payment_failed']['label'] = 'Something went wrong!';
}

/**
 * Alters payment method plugins.
 *
 * @param array $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_payment_method_alter(array &$definitions) {
  // Remvove a payment method plugin.
  unset($definitions['foo_plugin_id']);

  // Replace a payment method plugin with another.
  $definitions['foo_plugin_id']['class'] = 'Drupal\foo\FooPaymentMethod';
}

/**
 * Alters payment method configuration plugins.
 *
 * @param array $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_payment_method_configuration_alter(array &$definitions) {
  // Remvove a payment method configuration plugin.
  unset($definitions['foo_plugin_id']);

  // Replace a payment method configuration plugin with another.
  $definitions['foo_plugin_id']['class'] = 'Drupal\foo\FooPaymentMethodConfiguration';
}

/**
 * Alters payment method selector plugins.
 *
 * @param array $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_payment_method_selector_alter(array &$definitions) {
  // Remvove a payment method selector plugin.
  unset($definitions['foo_plugin_id']);

  // Replace a payment method selector plugin with another.
  $definitions['foo_plugin_id']['class'] = 'Drupal\foo\FooPaymentMethodSelector';
}

/**
 * Alters line item plugins.
 *
 * @param array $definitions
 *   Keys are plugin IDs. Values are plugin definitions.
 */
function hook_payment_line_item_alter(array &$definitions) {
}

/**
 * Alters payment type plugins.
 *
 * @param array $definitions
 *   Keys are plugein IDs. Values are plugin definitions.
 */
function hook_payment_type_alter(array &$definitions) {
}

/**
 * Responds to a payment status being set.
 *
 * @see Payment::setStatus()
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_STATUS_SET
 * @see \Drupal\payment\Event\PaymentStatusSet
 *
 * @param \Drupal\payment\Entity\PaymentInterface $payment
 * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface $previous_status
 *   The status the payment had before the new one was set. This may be
 *   identical to the current/new status.
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the \Drupal\payment\Event\PaymentEvents::PAYMENT_STATUS_SET Symfony
 * event instead.
 */
function hook_payment_status_set(PaymentInterface $payment, PaymentStatusInterface $previous_status = NULL) {
  // Notify the site administrator, for instance.
}

/**
 * Executes before the payment type's original context is resumed.
 *
 * @see \Drupal\payment\Plugin\Payment\Type\PaymentTypeBase::resumeContext()
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_TYPE_PRE_RESUME_CONTEXT
 * @see \Drupal\payment\Event\PaymentTypePreResumeContext
 *
 * @param \Drupal\payment\Entity\PaymentInterface $payment
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_RESUME_CONTEXT
 * Symfony event instead.
 */
function hook_payment_type_pre_resume_context(PaymentInterface $payment) {
  if ($payment->getStatus()->isOrHasAncestor('payment_success')) {
    drupal_set_message(t('Your payment was successfully completed.'));
  }
  else {
    drupal_set_message(t('Your payment was not completed.'));
  }
}

/**
 * Checks access for executing a payment.
 *
 * @param \Drupal\payment\Entity\PaymentInterface $payment
 *   $payment->getPaymentMethod() contains the method currently configured, but
 *   NOT the method that $payment should be tested against, which is
 *   $payment_method.
 * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface $payment_method
 * @param \Drupal\Core\Session\AccountInterface $account
 *
 * @return string
 *   \Drupal\Core\Access\AccessInterface::ALLOW,
 *   \Drupal\Core\Access\AccessInterface::DENY, or
 *   \Drupal\Core\Access\AccessInterface::KILL.
 *
 * @see \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase::executePaymentAccess()
 * @see \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase::executePaymentAccessEvent()
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_EXECUTE_ACCESS
 * @see \Drupal\payment\Event\PaymentExecuteAccess
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the \Drupal\payment\Event\PaymentEvents::PAYMENT_EXECUTE_ACCESS Symfony
 * event instead.
 */
function hook_payment_execute_access(PaymentInterface $payment, PaymentMethodInterface $payment_method, AccountInterface $account) {}

/**
 * Executes before a payment is executed.
 *
 * @param \Drupal\payment\Entity\PaymentInterface $payment
 *
 * @see \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase::executePayment()
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_EXECUTE
 * @see \Drupal\payment\Event\PaymentPreExecute
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_EXECUTE Symfony
 * event instead.
 */
function hook_payment_pre_execute(PaymentInterface $payment) {}

/**
 * Executes before a payment is captured.
 *
 * @param \Drupal\payment\Entity\PaymentInterface $payment
 *
 * @see \Drupal\payment\Plugin\Payment\Method\PaymentMethodCapturePaymentInterface::capturePayment()
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_CAPTURE
 * @see \Drupal\payment\Event\PaymentPreCapture
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_CAPTURE Symfony
 * event instead.
 */
function hook_payment_pre_capture(PaymentInterface $payment) {}

/**
 * Executes before a payment is refunded.
 *
 * @param \Drupal\payment\Entity\PaymentInterface $payment
 *
 * @see \Drupal\payment\Plugin\Payment\Method\PaymentMethodCapturePaymentInterface::capturePayment()
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_REFUND
 * @see \Drupal\payment\Event\PaymentPreRefund
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the \Drupal\payment\Event\PaymentEvents::PAYMENT_PRE_REFUND Symfony
 * event instead.
 */
function hook_payment_pre_refund(PaymentInterface $payment) {}

/**
 * Alters the IDs of payments available for referencing through an instance.
 *
 * @param string $category_id
 *   The ID of the category to load payment IDs for.
 * @param integer $owner_id
 *   The UID of the user for whom the payment should be available.
 * @param array $payment_ids
 *   The IDs of the payments that are available.
 *
 * @see \Drupal\payment\Event\PaymentEvents::PAYMENT_QUEUE_PAYMENT_IDS_ALTER
 * @see \Drupal\payment\Event\PaymentQueuePaymentIdsAlter
 *
 * @deprecated For proper dependency injection and testability, you are advised
 * to use the
 * \Drupal\payment\Event\PaymentEvents::PAYMENT_QUEUE_PAYMENT_IDS_ALTER Symfony
 * event instead.
 */
function hook_payment_queue_payment_ids_alter($category_id, $owner_id, array &$payment_ids) {
  // Add or remove payment IDs. All IDs MUST be stored in the queue before they
  // can be used.
}
