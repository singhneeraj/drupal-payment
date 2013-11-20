<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethod.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Plugin\payment\method\PaymentMethodInterface as PluginPaymentMethodInterface;

/**
 * Defines a payment method entity.
 *
 * @EntityType(
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
 *     "canonical" = "payment.payment_method.edit",
 *     "create-form" = "payment.payment_method.select",
 *     "edit-form" = "payment.payment_method.edit"
 *   },
 *   module = "payment"
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
   * The payment method plugin this entity uses.
   *
   * @var \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  protected $plugin;

  /**
   * The entity's UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * Implements __get().
   */
  public function __get($name) {
    if ($name == 'pluginId' && $this->getPlugin()) {
      return $this->getPlugin()->getPluginId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function bundle() {
    if ($this->getPlugin()) {
      return $this->getPlugin()->getPluginId();
    }
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
    $properties['pluginConfiguration'] = $this->getPlugin() ? $this->getPlugin()->getConfiguration() : array();
    $properties['pluginId'] = $this->getPlugin() ? $this->getPlugin()->getPluginId() : NULL;

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin(PluginPaymentMethodInterface $plugin) {
    $this->plugin = $plugin;
    $this->pluginId = $plugin->getPluginId();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->plugin;
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
  public function getOwnerId() {
    return $this->ownerId;
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
  public function currencies() {
    return $this->getPlugin()->currencies();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentFormElements(array $form, array &$form_state, PaymentInterface $payment) {
    return $this->getPlugin()->paymentFormElements($form, $form_state, $payment);
  }

  /**
   * {@inheritdoc}
   */
  function brands() {
    return $this->getPlugin()->brands();
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    $values += array(
      'ownerId' => (int) \Drupal::currentUser()->id(),
    );
  }

  /**
   * Clones the instance.
   */
  function __clone() {
    $this->setPlugin(clone $this->getPlugin());
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(PaymentInterface $payment, $payment_method_brand, AccountInterface $account) {
    return $this->getPlugin()->executePaymentAccess($payment, $payment_method_brand, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
    $this->getPlugin()->executePayment($payment);
  }
}
