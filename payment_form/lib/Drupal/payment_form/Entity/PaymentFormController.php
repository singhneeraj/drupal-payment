<?php

/**
 * @file
 * Contains Drupal\payment\Entity\PaymentFormController.
 */

namespace Drupal\payment_form\Entity;

use Drupal\Core\Entity\EntityFormController;
use Drupal\payment\Plugin\Payment\MethodSelector\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment form.
 */
class PaymentFormController extends EntityFormController {

  /**
   * The payment method selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\Manager
   */
  protected $paymentMethodSelectorManager;

  /**
   * Constructor.
   *
   * @param \Drupal\payment\Plugin\Payment\MethodSelector\Manager $payment_method_selector_manager
   */
  public function __construct(Manager $payment_method_selector_manager) {
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
    $form['payment_method'] = $this->paymentMethodSelectorManager->createInstance('payment_select')->formElements(array(), $form_state, $payment);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
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
