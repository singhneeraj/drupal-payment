<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethodInterface.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\payment\PaymentProcessingInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface as PluginPaymentMethodInterface;

/**
 * Defines payment methods.
 */
interface PaymentMethodInterface extends ConfigEntityInterface, PaymentProcessingInterface {

  /**
   * Sets the payment method controller plugin.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  public function setPlugin(PluginPaymentMethodInterface $plugin);

  /**
   * Gets the payment method controller plugin.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  public function getPlugin();

  /**
   * Sets the payment method ID.
   *
   * @see \Drupal\Core\Entity\EntityInterface::id()
   *
   * @param string $id
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  public function setId($id);

  /**
   * Sets the payment method UUID.
   *
   * @see \Drupal\Core\Entity\EntityInterface::uuid()
   *
   * @param string $uuid
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  public function setUuid($uuid);

  /**
   * Sets the human-readable label.
   *
   * @see \Drupal\Core\Entity\EntityInterface::label()
   *
   * @param string $label
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  public function setLabel($label);

  /**
   * Sets the owner's ID.
   *
   * @param int $id
   *
   * @return \Drupal\payment\Entity\PaymentMethodInterface
   */
  public function setOwnerId($id);

  /**
   * Gets the owner's ID.
   *
   * @return int
   */
  public function getOwnerId();
}
