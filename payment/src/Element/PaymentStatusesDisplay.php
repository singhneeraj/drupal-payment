<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentStatusesDisplay.
 */

namespace Drupal\payment\Element;

use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface;

/**
 * Provides form callbacks for the payment_line_item form element.
 */
class PaymentStatusesDisplay {

  /**
   * Implements form #pre_render callback.
   *
   * @throws \InvalidArgumentException
   */
  public static function preRender(array $element) {
    $statuses = $element['#statuses'];
    $element['table'] = array(
      '#empty' => t('There are no statuses.'),
      '#header' => array(t('Status'), t('Date')),
      '#type' => 'table',
    );
    foreach (array_reverse($statuses) as $i => $status) {
      if (!($status instanceof PaymentStatusInterface)) {
        throw new \InvalidArgumentException('A payment status does not implement \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
      }
      $definition = $status->getPluginDefinition();
      $element['table']['status_' . $i] = array(
        '#attributes' => array(
          'class' => array(
            'payment-status-plugin-' . $status->getPluginId(),
          ),
        ),
        'label' => array(
          '#attributes' => array(
            'class' => array('payment-status-label'),
          ),
          '#markup' => $definition['label'],
        ),
        'created' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-quantity'),
          ),
          '#markup' => format_date($status->getCreated()),
        ),
      );
    }

    return $element;
  }
}
