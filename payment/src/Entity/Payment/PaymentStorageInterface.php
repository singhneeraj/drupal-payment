<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentStorageInterface.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * A storage controller for payment entities.
 */
interface PaymentStorageInterface extends EntityStorageInterface {

  /**
   * Loads payment line items.
   *
   * @param array $ids
   *   Payment IDs.
   *
   * @return array
   *   Keys are payment IDs, values are arrays of which keys are line item names
   *   and values are \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface
   *   objects.
   */
  public function loadLineItems(array $ids);

  /**
   * Saves payment line items.
   *
   * @param array $line_items
   *   Keys are payment IDs, values are arrays of which keys are line item names
   *   and values are \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface
   *   objects.
   */
  public function saveLineItems(array $line_items);

  /**
   * Deletes payment line items.
   *
   * @param array $ids
   *   Keys are payment IDs. Values are line item names.
   */
  public function deleteLineItems(array $ids);

  /**
   * Loads payment statuses.
   *
   * @param array $ids
   *   Payment IDs.
   *
   * @return array
   *   Keys are payment IDs, values are arrays of
   *   \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface objects.
   */
  public function loadPaymentStatuses(array $ids);

  /**
   * Saves payment statuses.
   *
   * @param array $statuses
   *   Keys are payment IDs, values are arrays of
   *   \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface objects.
   */
  public function savePaymentStatuses(array $statuses);

  /**
   * Deletes payment statuses.
   *
   * @param array $ids
   *   Payment IDs.
   */
  public function deletePaymentStatuses(array $ids);
}
