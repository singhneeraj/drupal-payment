<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandler.
 */

namespace Drupal\payment\Entity\PaymentMethodConfiguration;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Checks access for payment method configurations.
 */
class PaymentMethodConfigurationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $payment_method, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method */
    if ($operation == 'enable') {
      return !$payment_method->status() && $payment_method->access('update', $account);
    }
    elseif ($operation == 'disable') {
      return $payment_method->status() && $payment_method->access('update', $account);
    }
    elseif ($operation == 'duplicate') {
      return $this->createAccess($payment_method->bundle(), $account) && $payment_method->access('view', $account);
    }
    else {
      $permission = 'payment.payment_method_configuration.' . $operation;
      return $account->hasPermission($permission . '.any') || $account->hasPermission($permission . '.own') && $payment_method->getOwnerId() == $account->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $bundle = NULL) {
    return $account->hasPermission('payment.payment_method_configuration.create.' . $bundle);
  }

  /**
   * {@inheritdoc}
   */
  protected function getCache($cid, $operation, $langcode, AccountInterface $account) {
    // Disable the cache, because the intensive operations are cached elsewhere
    // already and the results of all other operations are too volatile to
    // cache.
  }
}
