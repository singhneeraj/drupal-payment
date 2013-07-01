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
   */
  function create(array $values) {
    // @todo Remove access to global $user once https://drupal.org/node/2032553
    //has been fixed.
    global $user;

    $payment = parent::create($values);
    $payment->setOwnerId($user->id());

    return $payment;
  }

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
