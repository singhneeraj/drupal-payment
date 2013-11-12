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
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodInterface;

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
 *     "canonical" = "/admin/config/services/payment/method/{payment_method}",
 *     "create-form" = "/admin/config/services/payment/method-add",
 *     "edit-form" = "/admin/config/services/payment/method/{payment_method}"
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
    // @todo Remove access to global $user once https://drupal.org/node/2032553
    //has been fixed.
    global $user;

    $values += array(
      'ownerId' => (int) $user->id(),
    );
  }

  /**
   * {@inheritdoc}
   *
   * Copied from \Drupal\Core\Entity\EntityNG.
   *
   * @todo Remove this once https://drupal.org/node/1818574 is fixed.
   */
  public function uri($rel = 'canonical') {
    $entity_info = $this->entityInfo();

    $link_templates = isset($entity_info['links']) ? $entity_info['links'] : array();

    if (isset($link_templates[$rel])) {
      $template = $link_templates[$rel];
      $replacements = $this->uriPlaceholderReplacements();
      $uri['path'] = str_replace(array_keys($replacements), array_values($replacements), $template);

      // @todo Remove this once http://drupal.org/node/1888424 is in and we can
      //   move the BC handling of / vs. no-/ to the generator.
      $uri['path'] = trim($uri['path'], '/');

      // Pass the entity data to url() so that alter functions do not need to
      // look up this entity again.
      $uri['options']['entity_type'] = $this->entityType;
      $uri['options']['entity'] = $this;
      return $uri;
    }

    // For a canonical link (that is, a link to self), look up the stack for
    // default logic. Other relationship types are not supported by parent
    // classes.
    if ($rel == 'canonical') {
      return parent::uri();
    }
  }

  /**
   * Copied from \Drupal\Core\Entity\EntityNG.
   *
   * @todo Remove this once https://drupal.org/node/1818574 is fixed.
   */
  protected function uriPlaceholderReplacements() {
    if (empty($this->uriPlaceholderReplacements)) {
      $this->uriPlaceholderReplacements = array(
        '{entityType}' => $this->entityType(),
        '{bundle}' => $this->bundle(),
        '{id}' => $this->id(),
        '{uuid}' => $this->uuid(),
        '{' . $this->entityType() . '}' => $this->id(),
      );
    }
    return $this->uriPlaceholderReplacements;
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
