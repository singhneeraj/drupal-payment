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
      $brand_options = $payment->getPaymentMethod()->brandOptions();
      $payment->content['method'] = array(
        '#markup' => $brand_options[$payment->getPaymentMethodBrand()],
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
      $payment->content['links'] = array(
        '#theme' => 'links__payment',
        '#pre_render' => array('drupal_pre_render_links'),
        '#attributes' => array(
          'class' => array('links', 'inline'),
        ),
      );
      // Show the payment method plugin's payment operations as links.
      $links = array();
      $plugin = $payment->getPaymentMethod()->getPlugin();
      $definition = $plugin->getPluginDefinition();
      foreach ($definition['operations'] as $operation => $operation_info) {
        if ($plugin->paymentOperationAccess($payment, $operation, $payment->getPaymentMethodBrand())) {
          // @todo Add CSRF protection once https://drupal.org/node/1798296 is
          //   in.
          $uri = $payment->uri();
          $links[$operation] = array(
            'title' => $operation_info['label'],
            'href' => $uri['path'] . '/operation/' . $operation,
            'options' => $uri['options'],
          );
        }
      }
      $payment->content['links']['payment'] = array(
        '#theme' => 'links__payment__payment',
        '#links' => $links,
        '#attributes' => array(
          'class' => array('links', 'inline'),
        ),
      );
    }
  }
}
