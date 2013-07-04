<?php

/**
 * Contains \Drupal\payment\Plugin\payment\context\PaymentContextInterface .
 */

namespace Drupal\payment\Plugin\payment\context;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A payment context plugin.
 */
interface PaymentContextInterface extends PluginInspectionInterface {

  /**
   * Sets the ID of the payment this context is of.
   *
   * @param int $id
   * *
   * * @return \Drupal\payment\Plugin\payment\context\ContextInterface
   */
  public function setPaymentId($id);

  /**
   * Gets the ID of the payment this context is of.
   *
   * @param int
   */
  public function getPaymentId();

  /**
   * Returns the description of the payment this context is of.
   *
   * @param string
   */
  public function paymentDescription();

  /**
   * Resumes the payer's context workflow.
   */
  public function resume();
}
