<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PaymentStatusItem.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\payment\Payment;

/**
 * Provides a plugin bag for payment type plugins.
 *
 * @FieldType(
 *   id = "payment_status",
 *   label = @Translation("Payment status plugins")
 * )
 */
class PaymentStatusItem extends PaymentAwarePluginBagItemBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginManager() {
    // @todo Unit-test this.
    return Payment::statusManager();
  }

}
