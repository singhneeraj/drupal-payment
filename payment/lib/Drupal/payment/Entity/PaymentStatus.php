<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentStatus.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines a payment status entity.
 *
 * @EntityType(
 *   config_prefix = "payment.payment_status",
 *   controllers = {
 *     "access" = "Drupal\payment\Entity\PaymentStatusAccessController",
 *     "form" = {
 *       "default" = "Drupal\payment\Entity\PaymentStatusFormController",
 *       "delete" = "Drupal\payment\Entity\PaymentStatusDeleteFormController"
 *     },
 *     "storage" = "Drupal\payment\Entity\PaymentStatusStorageController"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   fieldable = FALSE,
 *   id = "payment_status",
 *   label = @Translation("Payment status"),
 *   links = {
 *     "canonical" = "/admin/config/services/payment/status/edit/{payment_status}",
 *     "create-form" = "/admin/config/services/payment/status/add",
 *     "edit-form" = "/admin/config/services/payment/status/edit/{payment_status}"
 *   },
 *   module = "payment"
 * )
 */
class PaymentStatus extends ConfigEntityBase implements PaymentStatusInterface {

  /**
   * The status' description.
   *
   * @var string
   */
  protected $description;

  /**
   * The entity's unique machine name.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable label.
   *
   * @var string
   */
  protected $label;

  /**
   * The plugin ID of the parent payment status.
   *
   * @var string
   */
  protected $parentId;

  /**
   * The entity's UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setParentId($id) {
    $this->parentId = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentId() {
    return $this->parentId;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getExportProperties() {
    $properties = parent::getExportProperties();
    $properties['id'] = $this->id();
    $properties['label'] = $this->label();
    $properties['parentId'] = $this->getParentId();
    $properties['description'] = $this->getDescription();

    return $properties;
  }
}
