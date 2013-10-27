<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentLineItemsDisplay.
 */

namespace Drupal\payment\Element;

use Drupal\payment\Entity\PaymentInterface;

/**
 * Provides form callbacks for the payment_line_item form element.
 */
class PaymentLineItemsDisplay {

  /**
   * Implements form #pre_render callback.
   *
   * @throws \InvalidArgumentException
   */
  public static function preRender(array $element) {
    $payment = $element['#payment'];
    if (!($payment instanceof PaymentInterface)) {
      throw new \InvalidArgumentException('The payment does not implement \Drupal\payment\Entity\PaymentInterface..');
    }
    $element['table'] = array(
      '#empty' => t('There are no line items.'),
      '#header' => array(t('Description'), t('Quantity'), t('Amount'), t('Total')),
      '#type' => 'table',
    );
    foreach ($payment->getLineItems() as $line_item) {
      $currency = entity_load('currency', $line_item->getCurrencyCode());
      $element['table']['line_item_' . $line_item->getName()] = array(
        '#attributes' => array(
          'class' => array(
            'payment-line-item',
            'payment-line-item-name-' . $line_item->getName(),
            'payment-line-item-plugin-' . $line_item->getPluginId(),
          ),
        ),
        'description' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-description'),
          ),
          '#markup' => $line_item->getDescription(),
        ),
        'quantity' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-quantity'),
          ),
          '#markup' => $line_item->getQuantity(),
        ),
        'amount' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-amount'),
          ),
          '#markup' => $currency->format($currency->roundAmount($line_item->getAmount())),
        ),
        'total' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-amount-total'),
          ),
          '#markup' => $currency->format($currency->roundAmount($line_item->getTotalAmount())),
        ),
      );
    }
    $currency = entity_load('currency', $payment->getCurrencyCode());
    $element['table']['payment_total'] = array(
      '#attributes' => array(
        'class' => array('payment-amount'),
      ),
      'label' => array(
        '#attributes' => array(
          'class' => array('payment-amount-label'),
          'colspan' => 3,
        ),
        '#markup' => t('Total amount'),
      ),
      'total' => array(
        '#attributes' => array(
          'class' => array('payment-amount-total'),
        ),
        '#markup' => $currency->format($currency->roundAmount($payment->getAmount())),
      ),
    );

    return $element;
  }
}
