<?php

/**
 * @file
 * Contains \Drupal\payment\Hook\Permission.
 */

namespace Drupal\payment\Hook;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Implements hook_permission().
 *
 * @see payment_permission()
 */
class Permission {

  use StringTranslationTrait;

  /**
   * The payment method configuration configuration manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $paymentMethodConfigurationManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Component\Plugin\PluginManagerInterface $payment_method_configuration_manager
   */
  public function __construct(TranslationInterface $string_translation, PluginManagerInterface $payment_method_configuration_manager) {
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Invokes the implementation.
   */
  public function invoke() {
    $permissions = array(
      'payment.payment.view.any' => array(
        'title' => $this->t('View any payment'),
      ),
      'payment.payment.view.own' => array(
        'title' => $this->t('View own payments'),
      ),
      'payment.payment.update.any' => array(
        'title' => $this->t('Update any payment'),
      ),
      'payment.payment.update.own' => array(
        'title' => $this->t('Update own payments'),
      ),
      'payment.payment.update_status.any' => array(
        'title' => $this->t("Update any payment's status"),
      ),
      'payment.payment.update_status.own' => array(
        'title' => $this->t("Update own payments' statuses"),
      ),
      'payment.payment.delete.any' => array(
        'title' => $this->t('Delete any payment'),
      ),
      'payment.payment.delete.own' => array(
        'title' => $this->t('Delete own payments'),
      ),
      'payment.payment.capture.any' => array(
        'title' => $this->t('Capture payments'),
      ),
      'payment.payment_method_configuration.update.any' => array(
        'title' => $this->t('Update any payment method configuration'),
        'restrict access' => TRUE,
      ),
      'payment.payment_method_configuration.update.own' => array(
        'title' => $this->t('Update own payment method configurations'),
      ),
      'payment.payment_method_configuration.delete.any' => array(
        'title' => $this->t('Delete any payment method configuration'),
      ),
      'payment.payment_method_configuration.delete.own' => array(
        'title' => $this->t('Delete own payment method configurations'),
      ),
      'payment.payment_method_configuration.view.any' => array(
        'title' => $this->t('View any payment method configuration'),
        'restrict access' => TRUE,
      ),
      'payment.payment_method_configuration.view.own' => array(
        'title' => $this->t('View own payment method configurations'),
      ),
      'payment.payment_status.administer' => array(
        'title' => $this->t('Administer payment statuses'),
      ),
      'payment.payment_type.administer' => array(
        'title' => $this->t('Administer payment types'),
      ),
    );
    $definitions = $this->paymentMethodConfigurationManager->getDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      $permissions['payment.payment_method_configuration.create.' . $plugin_id] = array(
        'title' => $this->t('Create %plugin_label payment method configurations', array(
          '%plugin_label' => $definition['label'],
        )),
      );
    }

    return $permissions;
  }

}
