<?php

/**
 * @file
 * Contains Drupal\payment\Entity\PaymentStorageControllerInterface.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * A storage controller for payment entities.
 */
interface PaymentStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Loads payment line items.
   *
   * @param array $ids
   *   Payment IDs.
   *
   * @return array
   *   Keys are payment IDs, values are arrays of which keys are line item names
   *   and values are \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   *   objects.
   */
  public function loadLineItems(array $ids);

  /**
   * Saves payment line items.
   *
   * @param array $lineItems
   *   Keys are payment IDs, values are arrays of which keys are line item names
   *   and values are \Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface
   *   objects.
   */
  public function saveLineItems(array $lineItems);

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
   *   \Drupal\payment\Plugin\payment\status\PaymentStatusInterface objects.
   */
  public function loadPaymentStatuses(array $ids);

  /**
   * Saves payment statuses.
   *
   * @param array $lineItems
   *   Keys are payment IDs, values are arrays of
   *   \Drupal\payment\Plugin\payment\status\PaymentStatusInterface objects.
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
