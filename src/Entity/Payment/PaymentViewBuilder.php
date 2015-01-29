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
      $build[$i]['line_items'] = array(
        '#payment' => $payment,
        '#type' => 'payment_line_items_display',
      );
    }
  }
}
