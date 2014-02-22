<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethod.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\user\UserInterface;

/**
 * Defines a payment method entity.
 *
 * @ConfigEntityType(
 *   bundle_label = @Translation("Payment method type"),
 *   config_prefix = "payment.payment_method",
 *   controllers = {
 *     "access" = "Drupal\payment\Entity\PaymentMethodAccessController",
 *     "form" = {
 *       "default" = "Drupal\payment\Entity\PaymentMethodFormController",
 *       "delete" = "Drupal\payment\Entity\PaymentMethodDeleteFormController"
 *     },
 *     "list" = "Drupal\payment\Entity\PaymentMethodListController",
 *     "storage" = "Drupal\payment\Entity\PaymentMethodStorageController",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "pluginId",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   fieldable = FALSE,
 *   id = "payment_method",
 *   label = @Translation("Payment method"),
 *   links = {
 *     "enable" = "payment.payment_method.enable",
 *     "disable" = "payment.payment_method.disable",
 *     "canonical" = "payment.payment_method.edit",
 *     "create-form" = "payment.payment_method.select",
 *     "edit-form" = "payment.payment_method.edit",
 *     "delete-form" = "payment.payment_method.delete",
 *     "duplicate-form" = "payment.payment_method.duplicate"
 *   }
 * )
 */
class PaymentMethod extends ConfigEntityBase implements PaymentMethodInterface {

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
   * The UID of the user this payment method belongs to.
   *
   * @var integer
   */
  protected $ownerId;

  /**
   * The configuration, which comes from the entity's payment method plugin.
   *
   * @var array
   */
  protected $pluginConfiguration = array();

  /**
   * The bundle, which is the ID of the entity's payment method plugin.
   *
   * @var integer
   */
  protected $pluginId;

  /**
   * The entity's UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * {@inheritdoc}
   */
  public function bundle() {
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\payment\PaymentMethodStorageController
   */
  public function getExportProperties() {
    $properties = parent::getExportProperties();
    $properties['id'] = $this->id();
    $properties['label'] = $this->label();
    $properties['ownerId'] = $this->getOwnerId();
    $properties['pluginId'] = $this->bundle();
    $properties['pluginConfiguration'] = $this->getPluginConfiguration();

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($id) {
    $this->ownerId = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $user) {
    $this->ownerId = $user->id();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->ownerId;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return \Drupal::entityManager()->getStorageController('user')->load($this->getOwnerId());
  }

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
  public function setUuid($uuid) {
    $this->uuid = $uuid;

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
  public function setPluginConfiguration(array $configuration) {
    $this->pluginConfiguration = $configuration;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginConfiguration() {
    return $this->pluginConfiguration;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    $values += array(
      'ownerId' => (int) \Drupal::currentUser()->id(),
    );
  }
}
