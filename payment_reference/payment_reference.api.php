<?php

/**
 * @file
 * Hook documentation.
 */

/**
 * Alters the IDs of payments available for referencing through an instance.
 *
 * @param string $field_instance_id
 *   The ID of the field instance to load payment IDs for.
 * @param integer $owner_id
 *   The UID of the user for whom the payment should be available.
 * @param array $payment_ids
 *   The IDs of the payments that are available.
 */
function hook_payment_reference_queue_payment_ids_alter($field_instance_id, $owner_id, array &$payment_ids) {
  // Add or remove payment IDs. All IDs MUST be stored in the queue before they
  // can be used.
}
