<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentListController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListController;

/**
 * Lists payment entities.
 */
class PaymentListController extends EntityListController {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['updated'] = t('Last updated');
    $row['status'] = t('Status');
    $row['amount'] = t('Amount');
    $row['payment_method'] = t('Payment method');
    $row['owner'] = t('Payer');
    $row['operations'] = t('Operations');

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $payment) {
    $row['data']['updated'] = format_date($payment->getChangedTime());

    $status_definition = $payment->getStatus()->getPluginDefinition();
    $row['data']['status'] = $status_definition['label'];

    $currency = entity_load('currency', $payment->getCurrencyCode());
    if (!$currency) {
      $currency = entity_load('currency', 'XXX');
    }
    $row['data']['amount'] = $currency->format($payment->getAmount());

    $payment_method = entity_load('payment_method', $payment->getPaymentMethodId());
    $row['data']['payment_method'] = $payment_method->label();

    $owner = entity_load('user', $payment->getOwnerId());
    $uri = $owner->uri();
    $row['data']['owner'] = l($owner->label(), $uri['path'], $uri['options']);

    $operations = $this->buildOperations($payment);
    $row['data']['operations']['data'] = $operations;

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $payment) {
    $operations = parent::getOperations($payment);

    // Show the payment method plugin's payment operations as links.
    $plugin = $payment->getPaymentMethod()->getPlugin();
    $definition = $plugin->getPluginDefinition();
    foreach ($definition['operations'] as $operation => $operation_info) {
      if ($plugin->paymentOperationAccess($payment, $operation, $payment->getPaymentMethodBrand())) {
        // @todo Add CSRF protection once https://drupal.org/node/1798296 is
        //   in.
        $uri = $payment->uri();
        $operations[$operation] = array(
          'title' => $operation_info['label'],
          'href' => $uri['path'] . '/operation/' . $operation,
          'options' => $uri['options'],
        );
      }
    }

    return $operations;
  }
}
