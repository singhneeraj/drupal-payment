<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder.
 */

namespace Drupal\payment\Entity\PaymentStatus;

use Drupal\Core\Entity\EntityListBuilder;

/**
 * Lists payment_status entities.
 */
class PaymentStatusListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    // The actual field instance config overview is rendered by
    // \Drupal\field_ui\FieldOverview, so we should not use this class to build
    // lists.
    throw new \Exception('This class is only used for operations and not for building lists.');
  }
}
