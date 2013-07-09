<?php

/**
 * @file
 * Definition of Drupal\payment\Plugin\Core\Entity\PaymentMethodAccessController.
 */

namespace Drupal\payment\Plugin\Core\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the default list controller for ConfigEntity objects.
 */
class PaymentAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $payment, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'create':
        // We let other modules decide whether users have access to create
        // new payments. There is no corresponding permission for this operation.
        return TRUE;
      default:
        return user_access('payment.payment.' . $operation . '.any', $account) || user_access('payment.payment.' . $operation . '.own', $account) && $account->id() == $payment->getOwnerId();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCache(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    // Disable the cache, because the intensive operations are cached in
    // user_access() already and the results of all other operations are too
    // volatile to be cached.
  }
}
