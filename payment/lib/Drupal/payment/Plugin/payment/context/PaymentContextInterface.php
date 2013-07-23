<?php

/**
 * Contains \Drupal\payment\Plugin\payment\context\PaymentContextInterface .
 */

namespace Drupal\payment\Plugin\payment\context;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\Plugin\Core\Entity\PaymentInterface;

/**
 * A payment context plugin.
 */
interface PaymentContextInterface extends PluginInspectionInterface {

  /**
   * Sets the ID of the payment this context is of.
   *
   * @param \Drupal\payment\Plugin\Core\Entity\PaymentInterface $payment
   *
   * @return \Drupal\payment\Plugin\payment\context\PaymentContextInterface
   */
  public function setPayment(PaymentInterface $payment);

  /**
   * Gets the ID of the payment this context is of.
   *
   * @return \Drupal\payment\Plugin\Core\Entity\PaymentInterface
   */
  public function getPayment();

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
