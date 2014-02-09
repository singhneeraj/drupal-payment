<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethodInterface.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines payment methods.
 */
interface PaymentMethodInterface extends ConfigEntityInterface, EntityOwnerInterface {

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
   * Sets the payment method's plugin configuration.
   *
   * @param array $configuration
   *
   * @return static
   */
  public function setPluginConfiguration(array $configuration);

  /**
   * Gets the payment method's plugin configuration.
   *
   * @return array
   */
  public function getPluginConfiguration();

  /**
   * Gets the payment method's plugin ID.
   *
   * @return string
   */
  public function getPluginId();
}
