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
class PaymentMethodAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'enable') {
      return !$entity->status() && $entity->access('update', $account);
    }
    elseif ($operation == 'disable') {
      return $entity->status() && $entity->access('update', $account);
    }
    elseif ($operation == 'duplicate') {
      return $entity->access('create', $account) && $entity->access('view', $account);
    }
    else {
      $permission = 'payment.payment_method.' . $operation;
      return user_access($permission . '.any', $account) || user_access($permission . '.own', $account) && $entity->getOwnerId() == $account->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return user_access('payment.payment_method.create.' . $entity_bundle, $account);
  }
}
