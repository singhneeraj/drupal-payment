<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A payment type plugin.
 */
interface PaymentTypeInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Sets the ID of the payment this plugin is of.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return static
   */
  public function setPayment(PaymentInterface $payment);

  /**
   * Gets the ID of the payment this plugin is of.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function getPayment();

  /**
   * Returns the description of the payment this plugin is of.
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
  public function resumeContext();
}
