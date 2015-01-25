<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PaymentAwarePluginBagItemBase.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\PaymentAwareInterface;

/**
 * Provides a plugin bag for payment-aware plugins.
 */
abstract class PaymentAwarePluginBagItemBase extends PluginBagItemBase {

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    if ($property_name == 'plugin_configuration') {
      $plugin_instance = $this->getContainedPluginInstance();
      if ($plugin_instance instanceof PaymentAwareInterface) {
        $data = $this;
        while ($data = $data->getParent()) {
          if ($data instanceof PaymentInterface) {
            $plugin_instance->setPayment($data);
            break;
          }
        }
      }
    }
    parent::onChange($property_name, $notify);
  }

}
