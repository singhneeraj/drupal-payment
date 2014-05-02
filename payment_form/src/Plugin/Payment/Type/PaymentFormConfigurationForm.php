<?php

/**
 * @file
 * Contains \Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm.
 */

namespace Drupal\payment_form\Plugin\Payment\Type;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration form for the payment_form payment type plugin.
 */
class PaymentFormConfigurationForm extends ConfigFormBase {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The payment method selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface
   */
  protected $paymentMethodSelectorManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface $payment_method_selector_manager
   */
  public function __construct(ConfigFactory $config_factory, PaymentMethodManagerInterface $payment_method_manager, PaymentMethodSelectorManagerInterface $payment_method_selector_manager) {
    parent::__construct($config_factory);
    $this->paymentMethodManager = $payment_method_manager;
    $this->paymentMethodSelectorManager = $payment_method_selector_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.payment.method'),
      $container->get('plugin.manager.payment.method_selector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_form_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->configFactory()->get('payment_form.payment_type');

    $form['payment_method_selector_id'] = array(
      '#default_value' => $config->get('payment_method_selector_id'),
      '#options' => $this->paymentMethodSelectorManager->options(),
      '#title' => $this->t('Payment method selector'),
      '#type' => 'radios',
    );
    // See \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface::getAllowedPaymentMethods().
    $allowed_payment_method_ids = $config->get('allowed_payment_method_ids');
    $form['allowed_payment_method_ids'] = array(
      '#default_value' => $allowed_payment_method_ids,
      '#description' => $this->t('If no methods are selected, all methods are allowed.'),
      '#multiple' => TRUE,
      '#options' => $this->paymentMethodManager->options(),
      '#title' => $this->t('Limit allowed payment methods'),
      '#type' => 'select',
    );

    return $form + parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = $this->configFactory->get('payment_form.payment_type');
    $config->set('payment_method_selector_id', $form_state['values']['payment_method_selector_id']);
    $config->set('limit_allowed_payment_methods', empty($form_state['values']['allowed_payment_method_ids']));
    $config->set('allowed_payment_method_ids', $form_state['values']['allowed_payment_method_ids']);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
