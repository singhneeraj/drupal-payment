<?php

/**
 * Contains \Drupal\payment\plugin\payment\line_item\LineItemInterface.
 */

namespace Drupal\payment\plugin\payment\line_item;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;

/**
 * A payment line item.
 */
interface LineItemInterface extends PluginInspectionInterface {

  /**
   * Sets the amount.
   *
   * @param float $amount
   *
   * @return \Drupal\payment\Plugin\payment\line_item\LineItemInterface
   */
  public function setAmount($amount);

  /**
   * Gets the amount.
   *
   * @return float
   */
  public function getAmount();

  /**
   * Return this line item's total amount.
   *
   * @return float
   */
  function getTotalAmount();

  /**
   * Sets the machine name.
   *
   * @param string $name
   *
   * @return \Drupal\payment\Plugin\payment\line_item\LineItemInterface
   */
  public function setName($name);

  /**
   * Gets the machine name.
   *
   * @return string
   */
  public function getName();

  /**
   * Gets the line item description.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Sets the payment ID.
   *
   * @param integer $id
   *
   * @return \Drupal\payment\Plugin\payment\line_item\LineItemInterface
   */
  public function setPaymentId($id);

  /**
   * Gets the payment ID.
   *
   * @return integer
   */
  public function getPaymentId();

  /**
   * Sets the quantity.
   *
   * @param int $quantity
   *
   * @return \Drupal\payment\Plugin\payment\line_item\LineItemInterface
   */
  public function setQuantity($quantity);

  /**
   * Gets the quantity.
   *
   * @return int
   */
  public function getQuantity();
}
