<?php

/**
 * @file
 * Contains Drupal\payment\Plugin\Core\entity\PaymentMethodStorageController.
 */

namespace Drupal\payment\Plugin\Core\entity;

use Drupal\Core\Config\Entity\ConfigStorageController;

/**
 * Handles storage for payment_method entities.
 */
class PaymentMethodStorageController extends ConfigStorageController {

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\payment\Plugin\Core\Entity\PaymentMethod::getExportProperties
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $payment_methods = parent::buildQuery($ids, $revision_id);
    $manager = \Drupal::service('plugin.manager.payment.payment_method');
    foreach ($payment_methods as $payment_method) {
      $payment_method->setPlugin($manager->createInstance($payment_method->pluginId), $payment_method->pluginConfiguration);
      unset($payment_method->pluginId);
      unset($payment_method->pluginConfiguration);
    }

    return $payment_methods;
  }
}
