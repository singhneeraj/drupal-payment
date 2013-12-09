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
   * Sets the ID of the payment this pluhin is of.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return self
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

  /**
   * Provides an array of information to build a list of operation links.
   *
   * @param string $plugin_id
   *   The ID of the plugin for which to get operations.
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
