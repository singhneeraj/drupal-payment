<?php

/**
 * @file
 * Contains Drupal\payment\PaymentMethodStorageController.
 */

namespace Drupal\payment;

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
    $manager = $this->container->get('plugin.manager.payment.payment_method');
    foreach ($payment_methods as $payment_method) {
      $payment_method->controller = $manager->createInstance($payment_method->controllerID, $payment_method->controllerConfiguration);
      $payment_method->controllerID == $payment_method->controllerConfiguration = NULL;
    }

    return $payment_methods;
  }
}
