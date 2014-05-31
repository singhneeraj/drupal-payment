<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentViewBuilder.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Builds payment views.
 */
class PaymentViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode, $langcode = NULL) {
    // @todo Extend \Drupal\Core\Entity\EntityViewBuilder with a
    //   doBuildComponents() method, so we can easily test this method without
    //   having to mock the parent's dependencies.
    /** @var \Drupal\payment\Entity\PaymentInterface[] $entities */

    parent::buildComponents($build, $entities, $displays, $view_mode, $langcode);

    foreach ($entities as $i => $payment) {
      $build[$i]['method'] = array(
        '#markup' => $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginLabel() : $this->t('Unavailable'),
        '#title' => $this->t('Payment method'),
        '#type' => 'item',
      );
      $build[$i]['line_items'] = array(
        '#payment' => $payment,
        '#type' => 'payment_line_items_display',
      );
      $build[$i]['statuses'] = array(
        '#statuses' => $payment->getStatuses(),
        '#type' => 'payment_statuses_display',
      );
    }
  }
}
