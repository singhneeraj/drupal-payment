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
class Config extends PaymentStatusBase {

  /**
   * {@inheritdoc}
   */
  public static function getOperations($plugin_id) {
    $entity_id = substr($plugin_id, 15);
    $entity = \Drupal::entityManager()->getStorage('payment_status')->load($entity_id);
    return \Drupal::entityManager()->getListBuilder('payment_status')->getOperations($entity);
    $operations = array();
    if (\Drupal::currentUser()->hasPermission('payment.payment_status.administer')) {

      // Strip the base plugin ID and the colon.
      $entity_id = substr($plugin_id, 15);
      $operations['update'] = array(
        'title' => t('Edit'),
        'route_name' => 'payment.payment_status.edit',
        'route_parameters' => array(
          'payment_status' => $entity_id,
        ),
      );
      $operations['delete'] = array(
        'title' => t('Delete'),
        'route_name' => 'payment.payment_status.delete',
        'route_parameters' => array(
          'payment_status' => $entity_id,
        ),
      );
    }

    return $operations;
  }
}
