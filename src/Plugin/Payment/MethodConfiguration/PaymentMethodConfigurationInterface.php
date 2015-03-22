<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface.
 */

namespace Drupal\payment\Plugin\Payment\MethodConfiguration;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * A payment method configuration plugin.
 */
interface PaymentMethodConfigurationInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

}
