<?php

/**
 * @file
 * Definition of Drupal\payment\Plugin\Core\entity\PaymentMethodInterface.
 */

namespace Drupal\payment\Plugin\Core\entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\payment\PaymentProcessingInterface;
use Drupal\payment\Plugin\payment\PaymentMethod\PaymentMethodInterface as PluginPaymentMethodInterface;

/**
 * Defines payment methods.
 */
interface PaymentMethodInterface extends EntityInterface, PaymentProcessingInterface {

  /**
   * Sets the payment method controller plugin.
   *
   * @param \Drupal\payment\Plugin\payment\PaymentMethod\PaymentMethodInterface
   */
  public function setPlugin(PluginPaymentMethodInterface $plugin);

  /**
   * Gets the payment method controller plugin.
   *
   * @return \Drupal\payment\Plugin\payment\PaymentMethod\PaymentMethodInterface
   */
  public function getPlugin();
}
