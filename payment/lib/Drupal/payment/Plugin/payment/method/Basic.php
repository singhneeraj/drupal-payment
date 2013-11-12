<?php

/**
 * Contains \Drupal\payment\Plugin\payment\method\Basic.
 */

namespace Drupal\payment\Plugin\payment\method;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\payment\status\Manager as PaymentStatusManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A basic payment method that does not transfer money.
 *
 * @PaymentMethod(
 *   description = @Translation("A payment method type that always successfully executes payments, but never actually transfers money."),
 *   id = "payment_basic",
 *   label = @Translation("Basic"),
 *   module = "payment"
 * )
 */
class Basic extends Base implements ContainerFactoryPluginInterface {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\payment\status\manager
   */
  protected $paymentStatusManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, Token $token, PaymentStatusManager $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $token);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('module_handler'), $container->get('token'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'brand_option' => '',
      'status' => '',
    );
  }

  /**
   * Sets the final payment status.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return \Drupal\payment\Plugin\payment\method\Basic
   */
  public function setStatus($status) {
    $this->configuration['status'] = $status;

    return $this;
  }

  /**
   * Gets the final payment status.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getStatus() {
    return $this->configuration['status'];
  }

  /**
   * {@inheritdoc}
   */
  public function paymentMethodFormElements(array $form, array &$form_state) {
    $elements = parent::paymentMethodFormElements($form, $form_state);
    $elements['#element_validate'][] = array($this, 'paymentMethodFormElementsValidateBasic');

    $elements['brand'] = array(
      '#default_value' => $this->configuration['brand_option'],
      '#description' => $this->t('The label that payers will see when choosing a payment method. Defaults to the payment method label.'),
      '#title' => $this->t('Brand label'),
      '#type' => 'textfield',
    );
    $elements['status'] = array(
      '#type' => 'select',
      '#title' => $this->t('Final payment status'),
      '#description' => $this->t('The status to give a payment after being processed by this payment method.'),
      '#default_value' => $this->getStatus() ? $this->getStatus() : 'payment_success',
      '#options' => $this->paymentStatusManager->options(),
    );

    return $elements;
  }

  /**
   * Implements form validate callback for self::paymentMethodFormElements().
   */
  public function paymentMethodFormElementsValidateBasic(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setStatus($values['status'])
      ->setBrandLabel($values['brand']);
  }

  /**
   * {@inheritdoc}
   */
  public function executePaymentAccess(PaymentInterface $payment, $payment_method_brand, AccountInterface $account = NULL) {
    return $payment_method_brand == 'default' && parent::executePaymentAccess($payment, $payment_method_brand, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
    if ($this->executePaymentAccess($payment, $payment->getPaymentMethodBrand())) {
      $payment->setStatus($this->paymentStatusManager->createInstance($this->getStatus()));
      $payment->save();
    }
    $payment->getPaymentType()->resumeContext();
  }

  /**
   * {@inheritdoc}
   */
  public function brands() {
    return array(
      'default' => array(
        'label' => $this->configuration['brand_option'] ? $this->configuration['brand_option'] : $this->getPaymentMethod()->label(),
      ),
    );
  }

  /**
   * Gets the brand option label.
   *
   * @return string
   */
  public function getBrandLabel() {
    return$this->configuration['brand_option'];
  }

  /**
   * Sets the brand option label.
   *
   * @param string $label
   *
   * @return \Drupal\payment\Plugin\payment\method\PaymentMethodInterface
   */
  public function setBrandLabel($label) {
    $this->configuration['brand_option'] = $label;

    return $this;
  }
}
