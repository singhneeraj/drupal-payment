<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Status\ConfigDerivative.
 */

namespace Drupal\payment\Plugin\Payment\Status;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieves payment status plugin definitions based on configuration entities.
 */
class ConfigDerivative extends DerivativeBase implements ContainerDerivativeInterface {

  /**
   * The payment status storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $paymentStatusStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityStorageControllerInterface $payment_status_storage) {
    $this->paymentStatusStorage = $payment_status_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('entity.manager')->getStorageController('payment_status'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    /** @var \Drupal\payment\Entity\PaymentStatusInterface[] $statuses */
    $statuses = $this->paymentStatusStorage->loadMultiple();
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
