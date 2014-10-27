<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic.
 */

namespace Drupal\payment\Plugin\Payment\MethodConfiguration;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration for the payment_basic payment method plugin.
 *
 * @PaymentMethodConfiguration(
 *   description = @Translation("A payment method type that always successfully executes payments, but never actually transfers money."),
 *   id = "payment_basic",
 *   label = @Translation("Basic")
 * )
 */
class Basic extends PaymentMethodConfigurationBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $string_translation, $module_handler);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $container->get('module_handler'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'brand_label' => '',
      'execute_status_id' => 'payment_pending',
      'capture' => FALSE,
      'capture_status_id' => 'payment_success',
      'refund' => FALSE,
      'refund_status_id' => 'payment_refunded',
    );
  }

  /**
   * Sets the status to set on payment execution.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return $this
   */
  public function setExecuteStatusId($status) {
    $this->configuration['execute_status_id'] = $status;

    return $this;
  }

  /**
   * Gets the status to set on payment execution.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getExecuteStatusId() {
    return $this->configuration['execute_status_id'];
  }

  /**
   * Sets the status to set on payment capture.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return $this
   */
  public function setCaptureStatusId($status) {
    $this->configuration['capture_status_id'] = $status;

    return $this;
  }

  /**
   * Gets the status to set on payment capture.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getCaptureStatusId() {
    return $this->configuration['capture_status_id'];
  }

  /**
   * Sets whether or not capture is supported.
   *
   * @param bool $capture
   *   Whether or not to support capture.
   *
   * @return $this
   */
  public function setCapture($capture) {
    $this->configuration['capture'] = $capture;

    return $this;
  }

  /**
   * Gets whether or not capture is supported.
   *
   * @param bool
   *   Whether or not to support capture.
   */
  public function getCapture() {
    return $this->configuration['capture'];
  }

  /**
   * Sets the status to set on payment refund.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return $this
   */
  public function setRefundStatusId($status) {
    $this->configuration['refund_status_id'] = $status;

    return $this;
  }

  /**
   * Gets the status to set on payment refund.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getRefundStatusId() {
    return $this->configuration['refund_status_id'];
  }

  /**
   * Sets whether or not refunds are supported.
   *
   * @param bool $refund
   *   Whether or not to support refunds.
   *
   * @return $this
   */
  public function setRefund($refund) {
    $this->configuration['refund'] = $refund;

    return $this;
  }

  /**
   * Gets whether or not refunds are supported.
   *
   * @param bool
   *   Whether or not to support refunds.
   */
  public function getRefund() {
    return $this->configuration['refund'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['plugin_form'] = array(
      '#process' => array(array($this, 'processBuildConfigurationForm')),
      '#type' => 'container',
    );

    return $form;
  }

  /**
   * Implements a form API #process callback.
   */
  public function processBuildConfigurationForm(array &$element, FormStateInterface $form_state, array &$form) {
    $element['brand_label'] = array(
      '#default_value' => $this->getBrandLabel(),
      '#description' => $this->t('The label that payers will see when choosing a payment method. Defaults to the payment method label.'),
      '#title' => $this->t('Brand label'),
      '#type' => 'textfield',
    );
    $labels = array();
    foreach ($this->paymentStatusManager->getDefinitions() as $definition) {
      $labels[$definition['id']] = (string) $definition['label'];
    }
    $workflow_group = implode('][', array_merge($element['#parents'], array('workflow')));
    $workflow_id = drupal_html_id('workflow');
    $element['workflow'] = array(
      '#attached' => array(
        'js' => array(
          drupal_get_path('module', 'payment') . '/js/payment.js',
          array(
            'data' => array(
              'payment' => array(
                'payment_method_configuration_basic' => $labels,
              ),
            ),
            'type' => 'setting',
          ),
        ),
      ),
      '#id' => $workflow_id,
      '#type' => 'vertical_tabs',
    );
    $element['execute'] = array(
      '#group' => $workflow_group,
      '#open' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Execution'),
    );
    $element['execute']['execute_status_id'] = array(
      '#default_value' => !$this->getExecuteStatusId() ?: $this->getExecuteStatusId(),
      '#description' => $this->t('The status to set payments to after being executed by this payment method.'),
      '#empty_value' => '',
      '#options' => $this->paymentStatusManager->options(),
      '#required' => TRUE,
      '#title' => $this->t('Payment execution status'),
      '#type' => 'select',
    );
    $element['capture'] = array(
      '#group' => $workflow_group,
      '#open' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Capture'),
    );
    $capture_id = drupal_html_id('capture');
    $element['capture']['capture'] = array(
      '#id' => $capture_id,
      '#type' => 'checkbox',
      '#title' => $this->t('Add an additional capture step after payments have been executed.'),
      '#default_value' => $this->getCapture(),
    );
    $element['capture']['capture_status_id'] = array(
      '#description' => $this->t('The status to set payments to after being captured by this payment method.'),
      '#default_value' => $this->getCaptureStatusId(),
      '#options' => $this->paymentStatusManager->options(),
      '#required' => TRUE,
      '#states' => array(
        'visible' => array(
          '#' . $capture_id => array(
            'checked' => TRUE,
          ),
        ),
      ),
      '#title' => $this->t('Payment capture status'),
      '#type' => 'select',
    );
    $refund_id = drupal_html_id('refund');
    $element['refund'] = array(
      '#group' => $workflow_group,
      '#open' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Refund'),
    );
    $element['refund']['refund'] = array(
      '#id' => $refund_id,
      '#type' => 'checkbox',
      '#title' => $this->t('Add an additional refund step after payments have been executed.'),
      '#default_value' => $this->getRefund(),
    );
    $element['refund']['refund_status_id'] = array(
      '#description' => $this->t('The status to set payments to after being refunded by this payment method.'),
      '#default_value' => $this->getRefundStatusId(),
      '#options' => $this->paymentStatusManager->options(),
      '#required' => TRUE,
      '#states' => array(
        'visible' => array(
          '#' . $refund_id => array(
            'checked' => TRUE,
          ),
        ),
      ),
      '#title' => $this->t('Payment refund status'),
      '#type' => 'select',
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $parents = $form['plugin_form']['brand_label']['#parents'];
    array_pop($parents);
    $values = $form_state->getValues();
    $values = NestedArray::getValue($values, $parents);
    $this->setExecuteStatusId($values['execute']['execute_status_id']);
    $this->setCapture($values['capture']['capture']);
    $this->setCaptureStatusId($values['capture']['capture_status_id']);
    $this->setRefund($values['refund']['refund']);
    $this->setRefundStatusId($values['refund']['refund_status_id']);
    $this->setBrandLabel($values['brand_label']);
  }

  /**
   * Gets the brand label.
   *
   * @return string
   */
  public function getBrandLabel() {
    return$this->configuration['brand_label'];
  }

  /**
   * Sets the brand label.
   *
   * @param string $label
   *
   * @return static
   */
  public function setBrandLabel($label) {
    $this->configuration['brand_label'] = $label;

    return $this;
  }

}
