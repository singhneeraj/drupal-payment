<?php

/**
 * Contains \Drupal\payment\plugin\payment\PaymentStatus\PaymentStatusInterface.
 */

namespace Drupal\payment\plugin\payment\status;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A payment status plugin.
 */
interface PaymentStatusInterface extends PluginInspectionInterface {

  /**
   * Sets the created date and time.
   *
   * @param string $created
   *   A Unix timestamp.
   * *
   * * @return \Drupal\payment\Plugin\payment\status\PaymentStatusInterface
   */
  public function setCreated($created);

  /**
   * Gets the created date and time.
   *
   * @return string
   *   A Unix timestamp.
   */
  public function getCreated();

  /**
   * Sets the ID of the payment this status is for.
   *
   * @param int $id
   * *
   * * @return \Drupal\payment\Plugin\payment\status\PaymentStatusInterface
   */
  public function setPaymentId($id);

  /**
   * Gets the ID of the payment this status is for.
   *
   * @param int
   */
  public function getPaymentId();

  /**
   * Sets the ID.
   *
   * @param int $id
   * *
   * * @return \Drupal\payment\Plugin\payment\status\PaymentStatusInterface
   */
  public function setId($id);

  /**
   * Gets the ID.
   *
   * @return int
   */
  public function getId();

  /**
   * Gets this payment status's ancestors.
   *
   * @return array
   *   The plugin IDs of this status's ancestors.
   */
  function getAncestors();

  /**
   * Gets this payment status's children.
   *
   * @return array
   *   The plugin IDs of this status's children.
   */
  public function getChildren();

  /**
   * Get this payment status's descendants.
   *
   * @return array
   *   The machine names of this status's descendants.
   */
  function getDescendants();

  /**
   * Checks if the status has a given other status as one of its ancestors.
   *.
   * @param string $plugin_id
   *   The payment status plugin ID to check against.
   *
   * @return boolean
   */
  function hasAncestor($plugin_id);

  /**
   * Checks if the status is equal to a given other status or has it one of
   * its ancestors.
   *
   * @param string $plugin_id
   *   The payment status plugin ID to check against.
   *
   * @return boolean
   */
  function isOrHasAncestor($plugin_id);
}
