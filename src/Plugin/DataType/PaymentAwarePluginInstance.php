<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\DataType\PaymentAwarePluginInstance.
 */

namespace Drupal\payment\Plugin\DataType;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\PaymentAwareInterface;

/**
 * Provides a payment-aware plugin instance data type.
 *
 * @DataType(
 *   id = "payment_aware_plugin_instance",
 *   label = @Translation("Plugin instance (payment-aware)")
 * )
 */
class PaymentAwarePluginInstance extends PluginInstance {

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    if ($value instanceof PaymentAwareInterface) {
      $this->setPayment($value);
    }
    parent::setValue($value, $notify);
  }

  /**
   * Sets a payment on a plugin instance.
   *
   * @param \Drupal\payment\PaymentAwareInterface $plugin_instance
   */
  protected function setPayment(PaymentAwareInterface $plugin_instance) {
    $data = $this;
    while ($data = $data->getParent()) {
      if ($data instanceof PaymentInterface) {
        $plugin_instance->setPayment($data);
        break;
      }
    }
  }

}
