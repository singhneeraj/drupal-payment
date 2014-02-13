<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentMethodStorageController.
 */

namespace Drupal\payment\Entity;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Core\Config\Entity\ConfigStorageController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles storage for payment_method entities.
 */
class PaymentMethodStorageController extends ConfigStorageController {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_info
   *   The entity type.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   The config storage service.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query_factory
   *   The entity query factory.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   */
  public function __construct(EntityTypeInterface $entity_info, ConfigFactory $config_factory, StorageInterface $config_storage, QueryFactory $entity_query_factory, UuidInterface $uuid_service, PaymentMethodManagerInterface $payment_method_manager) {
    parent::__construct($entity_info, $config_factory, $config_storage, $entity_query_factory, $uuid_service);
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_info) {
    return new static(
      $entity_info,
      $container->get('config.factory'),
      $container->get('config.storage'),
      $container->get('entity.query'),
      $container->get('uuid'),
      $container->get('plugin.manager.payment.method')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\payment\Entity\PaymentMethod::getExportProperties
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    /** @var \Drupal\payment\Entity\PaymentMethodInterface[] $payment_methods */
    $payment_methods = parent::buildQuery($ids, $revision_id);
    foreach ($payment_methods as $payment_method) {
      $payment_method->setOwnerId((int) $payment_method->getOwnerId());
    }

    return $payment_methods;
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    $return = parent::save($entity);
    if ($this->paymentMethodManager instanceof CachedDiscoveryInterface) {
      $this->paymentMethodManager->clearCachedDefinitions();
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    parent::delete($entities);
    if ($this->paymentMethodManager instanceof CachedDiscoveryInterface) {
      $this->paymentMethodManager->clearCachedDefinitions();
    }
  }
}
