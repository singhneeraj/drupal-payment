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
 * @ConfigEntityType(
 *   admin_permission = "payment.payment_status.administer",
 *   controllers = {
 *     "access" = "\Drupal\Core\Entity\EntityAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\payment\Entity\PaymentStatus\PaymentStatusForm",
 *       "delete" = "Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm"
 *     },
 *     "list_builder" = "Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder",
 *     "storage" = "\Drupal\Core\Config\Entity\ConfigEntityStorage"
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
 *     "canonical" = "payment.payment_status.edit",
 *     "create-form" = "payment.payment_status.add",
 *     "edit-form" = "payment.payment_status.edit",
 *     "delete-form" = "payment.payment_status.delete"
 *   }
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
  public function toArray() {
    $properties = parent::toArray();
    $properties['id'] = $this->id();
    $properties['label'] = $this->label();
    $properties['parentId'] = $this->getParentId();
    $properties['description'] = $this->getDescription();

    return $properties;
  }
}
