<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethodListController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\payment\Payment;

/**
 * Lists payment method entities.
 */
class PaymentMethodListController extends ConfigEntityListController {

  /**
   * Overrides Drupal\Core\Entity\EntityListController::load().
   */
  public function load() {
    $entities = parent::load();
    uasort($entities, array($this->entityInfo['class'], 'sort'));

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['label'] = t('Name');
    $row['plugin'] = t('Type');
    $row['owner'] = t('Owner');
    $row['status'] = t('Status');
    $row['operations'] = t('Operations');

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['data']['label'] = $entity->label();

    $plugin_definition = Payment::methodConfigurationManager()->getDefinition($entity->getPluginId());
    $row['data']['plugin'] = $plugin_definition['label'];

    $owner = entity_load('user', $entity->getOwnerId());
    $uri = $owner->uri();
    $row['data']['owner'] = l($owner->label(), $uri['path'], $uri['options']);

    $row['data']['status'] = $entity->status() ? t('Enabled') : t('Disabled');

    $operations = $this->buildOperations($entity);
    $row['data']['operations']['data'] = $operations;

    if (!$entity->status()) {
      $row['class']= array('payment-method-disabled');
    }

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    foreach (array('enable', 'disable') as $operation) {
      if (!$entity->access($operation)) {
        unset($operations[$operation]);
      }
    }
    $uri = $entity->uri();
    if ($entity->access('duplicate')) {
      $operations['duplicate'] = array(
        'href' => $uri['path'] . '/duplicate',
        'options' => $uri['options'],
        'title' => t('Duplicate'),
        'weight' => 99,
      );
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#attached']['css'][] = drupal_get_path('module', 'payment') . '/css/payment.css';
    $build['#attributes']['class'][] = 'payment-method-list';

    return $build;
  }
}
