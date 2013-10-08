<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentRenderController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityRenderController;

/**
 * Render controller for payments.
 */
class PaymentRenderController extends EntityRenderController {

  /**
   * Overrides Drupal\Core\Entity\EntityRenderController::buildContent().
   */
  public function buildContent(array $entities, array $displays, $view_mode, $langcode = NULL) {
    parent::buildContent($entities, $displays, $view_mode, $langcode);

    foreach ($entities as $payment) {
      if ($payment->getPaymentMethod()) {
        foreach ($payment->getPaymentMethod()->brands() as $payment_method_brand => $info) {
          $brand_options[$payment_method_brand] = $info['label'];
        }
        $brand_label = $brand_options[$payment->getPaymentMethodBrand()];
      }
      else {
        $brand_label = t('Unavailable');
      }
      $payment->content['method'] = array(
        '#markup' => $brand_label,
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
