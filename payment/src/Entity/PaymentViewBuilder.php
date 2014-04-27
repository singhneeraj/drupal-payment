<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentViewBuilder.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for payments.
 */
class PaymentViewBuilder extends EntityViewBuilder {

  /**
   * Overrides Drupal\Core\Entity\EntityRenderController::buildContent().
   */
  public function buildContent(array $entities, array $displays, $view_mode, $langcode = NULL) {
    /** @var \Drupal\payment\Entity\PaymentInterface[] $entities */
    parent::buildContent($entities, $displays, $view_mode, $langcode);

    foreach ($entities as $payment) {
      $payment->content['method'] = array(
        '#markup' => $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginLabel() : t('Unavailable'),
        '#title' => t('Payment method'),
        '#type' => 'item',
      );
      $payment->content['line_items'] = array(
        '#payment' => $payment,
        '#type' => 'payment_line_items_display',
      );
      $payment->content['statuses'] = array(
        '#statuses' => $payment->getStatuses(),
        '#type' => 'payment_statuses_display',
      );
    }
  }
}
