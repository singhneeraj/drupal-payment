<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PaymentMethodItem.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\payment\Payment;

/**
 * Provides a plugin collection for payment method plugins.
 *
 * @FieldType(
 *   id = "payment_method",
 *   label = @Translation("Payment method plugins")
 * )
 */
class PaymentMethodItem extends PaymentAwarePluginCollectionItemBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginManager() {
    // @todo Unit-test this.
    return Payment::methodManager();
  }

}
