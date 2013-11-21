<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\Config.
 */

namespace Drupal\payment\Plugin\Payment\Status;

/**
 * A status based on a configuration entity.
 *
 * @PaymentStatus(
 *   derivative = "Drupal\payment\Plugin\Payment\Status\ConfigDerivative",
 *   id = "payment_config",
 *   label = @Translation("Configuration entity status")
 * )
 */
class Config extends Base {

  /**
   * {@inheritdoc}
   */
  public static function getOperations($plugin_id) {
    $operations = array();
    if (\Drupal::currentUser()->hasPermission('payment.payment_status.administer')) {

      // Strip the base plugin ID and the colon.
      $entity_id = substr($plugin_id, 15);
      $operations['update'] = array(
        'title' => t('Edit'),
        'href' => 'admin/config/services/payment/status/edit/' . $entity_id,
      );
      $operations['delete'] = array(
        'title' => t('Delete'),
        'href' => 'admin/config/services/payment/status/delete/' . $entity_id,
      );
    }

    return $operations;
  }
}
