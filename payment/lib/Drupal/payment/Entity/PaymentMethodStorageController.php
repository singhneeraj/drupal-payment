<?php

/**
 * @file
 * Contains Drupal\payment\Entity\PaymentMethodStorageController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\Entity\ConfigStorageController;

/**
 * Handles storage for payment_method entities.
 */
class PaymentMethodStorageController extends ConfigStorageController {

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\payment\Entity\PaymentMethod::getExportProperties
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $payment_methods = parent::buildQuery($ids, $revision_id);
    $manager = \Drupal::service('plugin.manager.payment.method');
    foreach ($payment_methods as $payment_method) {
      $configuration = $payment_method->pluginConfiguration;
      $plugin = $manager->createInstance($payment_method->pluginId, $configuration);
      $plugin->setPaymentMethod($payment_method);
      $payment_method->setPlugin($plugin);
      unset($payment_method->pluginId);
      unset($payment_method->pluginConfiguration);
      $payment_method->setOwnerId((int) $payment_method->getOwnerId());
    }

    return $payment_methods;
  }

  /**
   * {@inheritdoc}
   */
  public function importCreate($name, Config $new_config, Config $old_config) {
    $payment_method_data = $new_config->get();
    unset($payment_method_data['pluginConfiguration']);
    unset($payment_method_data['pluginId']);
    $payment_method = $this->create($payment_method_data);
    $manager = \Drupal::service('plugin.manager.payment.method');
    $configuration = $new_config->get('pluginConfiguration');
    $plugin = $manager->createInstance($new_config->get('pluginId'), $configuration);
    $plugin->setPaymentMethod($payment_method);
    $payment_method->setPlugin($plugin);
    $payment_method->save();

    return TRUE;
  }
}
