<?php

/**
 * @file
 * Definition of Drupal\payment\Plugin\Core\Entity\PaymentMethod.
 */

namespace Drupal\payment\Plugin\Core\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\payment\Plugin\payment\method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\Core\entity\Payment;
use Drupal\payment\Plugin\Core\entity\PaymentMethodInterface;

/**
 * Defines a payment method entity.
 *
 * @EntityType(
 *   config_prefix = "payment.payment_method",
 *   controllers = {
 *     "access" = "Drupal\payment\Plugin\Core\entity\PaymentMethodAccessController",
 *     "storage" = "Drupal\payment\Plugin\Core\entity\PaymentMethodStorageController",
 *   },
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   fieldable = FALSE,
 *   id = "payment_method",
 *   label = @Translation("Payment method"),
 *   module = "payment"
 * )
 */
class PaymentMethod extends ConfigEntityBase implements PaymentMethodInterface {

  /**
   * The payment method plugin this entity uses.
   *
   * @var \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  public $plugin;

  /**
   * The configuration of the payment method plugin in self::plugin.
   *
   * This property exists for storage purposes only.
   *
   * @var array
   */
  protected $pluginConfiguration;

  /**
   * The plugin ID of the payment method plugin in self::plugin.
   *
   * This property exists for storage purposes only.
   *
   * @var string
   */
  protected $pluginID;

  /**
   * The entity's unique machine name.
   *
   * @var string
   */
  public $name = NULL;

  /**
   * The human-readable label.
   *
   * @var string
   */
  public $label = NULL;

  /**
   * The UID of the user this payment method belongs to.
   *
   * @var integer
   */
  public $uid = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    global $user;

    parent::__construct($values, $entity_type);
    if (is_null($this->uid)) {
      $this->uid = $user->uid;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\payment\PaymentMethodStorageController
   */
  public function getExportProperties() {
    $properties = parent::getExportProperties();
    $properties['pluginConfiguration'] = $this->getPlugin()->getConfiguration();
    $properties['pluginID'] = $this->getPlugin()->getPluginId();

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin(PluginPaymentMethodInterface $plugin) {
    $this->plugin = $plugin;
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
  public function currencies() {
    return $this->getPlugin()->currencies();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentFormElements(array $form, array &$form_state) {
    return $this->getPlugin()->paymentFormElements($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validatePayment(Payment $payment) {
    return $this->getPlugin()->validatePayment($payment);
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(Payment $payment) {
    return $this->getPlugin()->executePayment($payment);
  }
}
