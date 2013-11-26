<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Status\ConfigDerivative.
 */

namespace Drupal\payment\Plugin\Payment\Status;

use Drupal\Component\Plugin\Derivative\DerivativeBase;

/**
 * Retrieves payment status plugin definitions based on configuration entities.
 */
class ConfigDerivative extends DerivativeBase {

  /**
   * The payment status storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $storage;

  /**
   * Returns the payment status storage controller.
   */
  protected function getPaymentStatusStorage() {
    if (!$this->storage) {
      $this->storage = \Drupal::entityManager()->getStorageController('payment_status');
    }

    return $this->storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    $statuses = $this->getPaymentStatusStorage()->loadMultiple();
    foreach ($statuses as $status) {
      $this->derivatives[$status->id()] = array(
        'description' => $status->getDescription(),
        'label' => $status->label(),
        'parent_id' => $status->getParentId(),
      ) + $base_plugin_definition;
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }
}
