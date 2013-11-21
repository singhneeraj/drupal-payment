<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentStatusStorageController.
 */

namespace Drupal\payment\Entity;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\Entity\ConfigStorageController;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\payment\Plugin\Payment\Status\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles storage for payment_status entities.
 */
class PaymentStatusStorageController extends ConfigStorageController {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity_type, array $entity_info, ConfigFactory $config_factory, StorageInterface $config_storage, QueryFactory $entity_query_factory, Manager $payment_status_manager, UuidInterface $uuid_service) {
    parent::__construct($entity_type, $entity_info, $config_factory, $config_storage, $entity_query_factory, $uuid_service);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, $entity_type, array $entity_info) {
    return new static($entity_type, $entity_info,$container->get('config.factory'), $container->get('config.storage'), $container->get('entity.query'), $container->get('plugin.manager.payment.status'), $container->get('uuid'));
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    parent::save($entity);
    $this->paymentStatusManager->clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    parent::delete($entities);
    $this->paymentStatusManager->clearCachedDefinitions();
  }
}
