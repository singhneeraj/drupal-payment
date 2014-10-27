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
   * @param int[] $ids
   *   Payment IDs.
   *
   * @return array[]
   *   Keys are payment IDs, values are arrays of which keys are line item names
   *   and values are \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface
   *   objects.
   */
  public function loadLineItems(array $ids);

  /**
   * Saves payment line items.
   *
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[] $line_items
   */
  public function saveLineItems(array $line_items);

  /**
   * Deletes payment line items.
   *
   * @param string[] $ids
   *   Keys are payment IDs. Values are line item names.
   */
  public function deleteLineItems(array $ids);

  /**
   * Loads payment statuses.
   *
   * @param int[] $ids
   *   Payment IDs.
   *
   * @return array[]
   *   Keys are payment IDs, values are arrays of
   *   \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface objects.
   */
  public function loadPaymentStatuses(array $ids);

  /**
   * Saves payment statuses.
   *
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface[] $statuses
   */
  public function savePaymentStatuses(array $statuses);

  /**
   * Deletes payment statuses.
   *
   * @param int[] $ids
   *   Payment IDs.
   */
  public function deletePaymentStatuses(array $ids);
}
