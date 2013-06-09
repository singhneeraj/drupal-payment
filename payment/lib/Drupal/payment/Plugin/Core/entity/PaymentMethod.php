<?php

/**
 * @file
 * Definition of Drupal\payment\Plugin\Core\Entity\PaymentMethod.
 */

namespace Drupal\payment\Plugin\Core\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\payment\PaymentMethodInterface;

/**
 * Defines a payment method entity.
 *
 * @EntityType(
 *   config_prefix = "payment.payment_method",
 *   controllers = {
 *     "access" = "Drupal\payment\PaymentMethodAccessController",
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
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
   * The payment method controller this merchant uses.
   *
   * @var PaymentMethodController
   */
  public $controller = NULL;

  /**
   * Information about this payment method that is specific to its controller.
   *
   * @var array
   */
  public $controller_data = array();

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
   */
  public function validatePayment(\Payment $payment, $strict = TRUE) {
    $this->controller->validate($payment, $this, $strict);
    module_invoke_all('payment_validate', $payment, $this, $strict);
    if (module_exists('rules')) {
      rules_invoke_event('payment_validate', $payment, $this, $strict);
    }
  }
}
