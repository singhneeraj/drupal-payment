<?php

/**
 *
 */

namespace Drupal\payment_reference\Plugin\Payment\Type;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration form for the payment_reference payment type plugin.
 */
class PaymentReferenceConfigurationForm extends ConfigFormBase {

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface $payment_method_selector_manager
   */
  public function __construct(ConfigFactoryInterface $config_factory, TranslationInterface $string_translation, PaymentMethodManagerInterface $payment_method_manager, PaymentMethodSelectorManagerInterface $payment_method_selector_manager) {
    parent::__construct($config_factory);
    $this->paymentMethodManager = $payment_method_manager;
    $this->paymentMethodSelectorManager = $payment_method_selector_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('string_translation'),
      $container->get('plugin.manager.payment.method'),
      $container->get('plugin.manager.payment.method_selector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_reference_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory()->get('payment_reference.payment_type');
    $form['payment_method_selector_id'] = array(
      '#default_value' => $config->get('payment_method_selector_id'),
      '#options' => $this->paymentMethodSelectorManager->options(),
      '#title' => $this->t('Payment method selector'),
      '#type' => 'radios',
    );
    $limit_allowed_payment_methods_id = drupal_html_id('limit_allowed_payment_methods');
    $form['limit_allowed_payment_methods'] = array(
      '#default_value' => $config->get('limit_allowed_payment_methods'),
      '#id' => $limit_allowed_payment_methods_id,
      '#title' => $this->t('Limit allowed payment methods'),
      '#type' => 'checkbox',
    );
    $allowed_payment_method_ids = $config->get('allowed_payment_method_ids');
    $form['allowed_payment_method_ids'] = array(
      '#default_value' => $allowed_payment_method_ids,
      '#multiple' => TRUE,
      '#options' => $this->paymentMethodManager->options(),
      '#states' => array(
        'visible' => array(
          '#' . $limit_allowed_payment_methods_id => array(
            'checked' => TRUE,
          ),
        ),
      ),
      '#title' => $this->t('Allowed payment methods'),
      '#type' => 'select',
    );

    return $form + parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('payment_reference.payment_type');
    $values = $form_state->getValues();
    $config->set('payment_method_selector_id', $values['payment_method_selector_id']);
    $config->set('limit_allowed_payment_methods', $values['limit_allowed_payment_methods']);
    $config->set('allowed_payment_method_ids', $values['allowed_payment_method_ids']);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
