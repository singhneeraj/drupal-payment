<?php

/**
 * @file
 * Contains \Drupal\payment_reference\QueueInterface.
 */

namespace Drupal\payment_reference;

/**
 * Defines a payment reference queue manager.
 */
interface QueueInterface {

  /**
   * Saves a payment available for referencing through a field instance.
   *
   * @param string $field_instance_id
   * @param integer $payment_id
   */
  public function save($field_instance_id, $payment_id);

  /**
   * Claims a payment available for referencing through a field instance.
   *
   * After a payment has been claimed, it can be definitely acquired with
   * self::acquire().
   *
   * @param integer $payment_id
   *
   * @return string|false
   *   An acquisition code to acquire the payment with on success, or FALSE if
   *   the payment could not be claimed.
   */
  public function claim($payment_id);

  /**
   * Releases a claimed payment.
   *
   * @param integer $payment_id
   * @param string $acquisition_code
   *   The code that was received from self::claim().
   */
  public function release($payment_id, $acquisition_code);

  /**
   * Acquires a payment and removes if from the queue.
   *
   * @param integer $payment_id
   * @param string $acquisition_code
   *   The code that was received from self::reserve().
   *
   * @return bool
   *   Whether the acquisition was successful.
   */
  public function acquire($payment_id, $acquisition_code);

  /**
   * Checks if a payment is available for referencing.
   *
   * @param integer $payment_id
   *   The ID of the payment to check.
   * @param integer $owner_id
   *   The UID of the user that should be the payment's owner.
   *
   * @return string|null
   *   NULL if the payment is not available. Otherwise it is the ID of the
   *   field instance the payment is available for.
   *
   * @todo Consider removing this, as payments have an entity reference to field
   *   instances that can be used.
   */
  public function loadFieldInstanceId($payment_id, $owner_id);

  /**
   * Loads the IDs of payments available for referencing through an instance.
   *
   * @param string $field_instance_id
   *   The ID of the field instance to load payment IDs for.
   * @param integer $owner_id
   *   The UID of the user for whom the payment should be available.
   *
   * @return array
   */
  public function loadPaymentIds($field_instance_id, $owner_id);

  /**
   * Deletes a payment from the queue by payment ID.
   *
   * @param integer $id
   */
  public function deleteByPaymentId($id);

  /**
   * Deletes payments from the queue by field ID.
   *
   * @param string $field_id
   */
  public function deleteByFieldId($field_id);

  /**
   * Deletes payments from the queue by field instance ID.
   *
   * @param string $field_instance_id
   */
  public function deleteByFieldInstanceId($field_instance_id);
}
