<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\LineItemItem.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\payment\Payment;

/**
 * Provides a plugin bag for payment line item plugins.
 *
 * @FieldType(
 *   id = "payment_line_item",
 *   label = @Translation("Payment line item plugins")
 * )
 */
class LineItemItem extends PaymentAwarePluginBagItemBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginManager() {
    // @todo Unit-test this.
    return Payment::lineItemManager();
  }

}
