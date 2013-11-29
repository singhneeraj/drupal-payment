<?php

/**
 * Contains \Drupal\payment\plugin\payment\PaymentStatus\PaymentStatusInterface.
 */

namespace Drupal\payment\plugin\payment\status;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A payment status plugin.
 */
interface PaymentStatusInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Sets the created date and time.
   *
   * @param string $created
   *   A Unix timestamp.
   * *
   * @return self
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
   *
   * @return self
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
   * @return self
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

  /**
   * Provides an array of information to build a list of operation links.
   *
   * @return array
   *   An associative array of operation link data for this list, keyed by
   *   operation name, containing the following key-value pairs:
   *   - title: The localized title of the operation.
   *   - href: The path for the operation.
   *   - options: An array of URL options for the path.
   *   - weight: The weight of this operation.
   */
  public static function getOperations($plugin_id);
}
