<?php

/**
 * Contains \Drupal\payment\Plugin\payment\context\PaymentContextInterface .
 */

namespace Drupal\payment\Plugin\payment\context;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A payment context plugin.
 */
interface PaymentContextInterface extends PluginInspectionInterface {

  /**
   * Sets the ID of the payment this context is of.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return \Drupal\payment\Plugin\payment\context\PaymentContextInterface
   */
  public function setPayment(PaymentInterface $payment);

  /**
   * Gets the ID of the payment this context is of.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment();

  /**
   * Returns the description of the payment this context is of.
   *
   * @param string $language_code
   *   The code of the language to return the description in.
   *
   * @param string
   */
  public function paymentDescription($language_code = NULL);

  /**
   * Resumes the payer's context workflow.
   */
  public function resume();

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
  public static function getOperations();
}
