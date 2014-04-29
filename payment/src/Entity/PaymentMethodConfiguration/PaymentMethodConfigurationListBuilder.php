<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationListBuilder.
 */

namespace Drupal\payment\Entity\PaymentMethodConfiguration;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\payment\Payment;

/**
 * Lists payment method configurations..
 */
class PaymentMethodConfigurationListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['label'] = $this->t('Name');
    $row['plugin'] = $this->t('Type');
    $row['owner'] = array(
      'data' => t('Owner'),
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );
    $row['status'] = array(
      'data' => t('Status'),
      'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
    );
    $row['operations'] = $this->t('Operations');

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method */
    $payment_method = $entity;

    $row['data']['label'] = $payment_method->label();

    $plugin_definition = Payment::methodConfigurationManager()->getDefinition($payment_method->getPluginId());
    $row['data']['plugin'] = isset($plugin_definition['label']) ? $plugin_definition['label'] : $this->t('Unknown');

    $owner_label = $payment_method->getOwner()->label();
    if ($payment_method->getOwner()->access('view')) {
      $owner_label = \Drupal::linkGenerator()->generateFromUrl($owner_label, $payment_method->getOwner()->urlInfo());
    }
    $row['data']['owner'] = $owner_label;

    $row['data']['status'] = $payment_method->status() ? $this->t('Enabled') : $this->t('Disabled');

    $operations = $this->buildOperations($entity);
    $row['data']['operations']['data'] = $operations;

    if (!$payment_method->status()) {
      $row['class']= array('payment-method-configuration-disabled');
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
    if ($entity->access('duplicate')) {
      $operations['duplicate'] = array(
        'title' => $this->t('Duplicate'),
        'weight' => 99,
      ) + $entity->urlInfo('duplicate-form')->toArray();
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#attached']['css'][] = drupal_get_path('module', 'payment') . '/css/payment.css';
    $build['#attributes']['class'][] = 'payment-method-configuration-list';

    return $build;
  }
}
