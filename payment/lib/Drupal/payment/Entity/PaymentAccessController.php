<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentAccessController.
 */

namespace Drupal\payment\Entity;

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
