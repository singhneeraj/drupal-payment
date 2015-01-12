<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Action\SetStatus.
 */

namespace Drupal\payment\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sets a status on a payment.
 *
 * @Action(
 *   id = "payment_set_status",
 *   label = @Translation("Set payment status"),
 *   type = "payment"
 * )
 */
class SetStatus extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, PaymentStatusManagerInterface $payment_status_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->paymentStatusManager = $payment_status_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function execute(PaymentInterface $payment = NULL) {
    if ($payment) {
      $status = $this->paymentStatusManager->createInstance($this->configuration['payment_status_plugin_id']);
      $payment->setPaymentStatus($status);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'payment_status_plugin_id' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['payment_status_plugin_id'] = array(
      '#default_value' => $this->configuration['payment_status_plugin_id'],
      '#options' => $this->paymentStatusManager->options(),
      '#required' => TRUE,
      '#title' => $this->t('Payment status'),
      '#type' => 'select',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['payment_status_plugin_id'] = $values['payment_status_plugin_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function access($payment, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($payment instanceof PaymentInterface) {
      return $payment->access('update', $account, $return_as_object);
    }
    $access = AccessResult::forbidden();
    return $return_as_object ? $access : $access->isAllowed();
  }

}
