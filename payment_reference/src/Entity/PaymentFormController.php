<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Entity\PaymentFormController.
 */

namespace Drupal\payment_reference\Entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment form.
 */
class PaymentFormController extends EntityForm {

  /**
   * The payment method selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface
   */
  protected $paymentMethodSelectorManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface $payment_method_selector_manager
   */
  public function __construct(PaymentMethodSelectorManagerInterface $payment_method_selector_manager) {
    $this->paymentMethodSelectorManager = $payment_method_selector_manager;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.payment.method_selector'));
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $payment = $this->getEntity();
    $form['line_items'] = array(
      '#payment' => $payment,
      '#type' => 'payment_line_items_display',
    );
    $config = $this->config('payment_reference.payment_type');
    $payment_method_selector_id = $config->get('payment_selector_id');
    $limit_allowed_payment_methods = $config->get('limit_allowed_payment_methods');
    $allowed_payment_method_ids = $config->get('allowed_payment_method_ids');
    $payment_method_selector = $this->paymentMethodSelectorManager->createInstance($payment_method_selector_id);
    if ($limit_allowed_payment_methods) {
      $payment_method_selector->setAllowedPaymentMethods($allowed_payment_method_ids);
    }
    $form['payment_method'] = $payment_method_selector->formElements(array(), $form_state, $payment);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();
    $payment_method = $this->paymentMethodSelectorManager->createInstance('payment_select')->getPaymentMethodFromFormElements($form['payment_method'], $form_state);
    $payment->setPaymentMethod($payment_method);
    $payment->save();
    $payment->execute();
  }

  /**
   * Returns an array of supported actions for the current entity form.
   */
  protected function actions(array $form, array &$form_state) {
    // Only use the existing submit action.
    $actions = parent::actions($form, $form_state);
    $actions = array(
      'submit' => $actions['submit'],
    );
    $actions['submit']['#value'] = $this->t('Pay');

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    return $this->getEntity();
  }
}
