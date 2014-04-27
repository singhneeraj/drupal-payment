<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface.
 */

namespace Drupal\payment\Plugin\Payment\MethodConfiguration;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A payment method configuration plugin.
 */
interface PaymentMethodConfigurationInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Gets the plugin label.
   *
   * @return string
   */
  public function getPluginLabel();

  /**
   * Gets the plugin description.
   *
   * @return string
   */
  public function getPluginDescription();

  /**
   * Returns the form elements to configure payment methods.
   *
   * Existing configuration can be set and retrieved through
   * self::setConfiguration() and self::getConfiguration().
   *
   * @param array $form
   * @param array $form_state
   *
   * @return array
   *   A render array.
   */
  public function formElements(array $form, array &$form_state);
}
