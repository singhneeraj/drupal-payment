<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentSelectPaymentMethodSelectorForm.
 */

namespace Drupal\payment_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\payment\Generate;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to test the payment_select payment method selector plugin.
 */
class PaymentSelectPaymentMethodSelectorForm implements ContainerInjectionInterface, FormInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The payment method selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface
   */
  protected $paymentMethodSelectorManager;

  /**
   * The payment method type manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface
   */
  protected $paymentTypeManager;

  /**
   * Constructor.
   */
  function __construct(EntityManagerInterface $entity_manager, PaymentTypeManagerInterface $payment_type_manager, PaymentMethodSelectorManagerInterface $payment_method_selector_manager) {
    $this->entityManager = $entity_manager;
    $this->paymentTypeManager = $payment_type_manager;
    $this->paymentMethodSelectorManager = $payment_method_selector_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'), $container->get('plugin.manager.payment.type'), $container->get('plugin.manager.payment.method_selector'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'payment_test_payment_method_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->entityManager->getStorageController('payment')->create(array(
      'bundle' => 'payment_unavailable',
    ));
    $payment->setLineItems(Generate::createPaymentLineItems());
    $form['payment_method'] = $this->paymentMethodSelectorManager->createInstance('payment_select')->formElements(array(), $form_state, $payment);
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    \Drupal::state()->set('payment_test_method_form_element', $this->paymentMethodSelectorManager->createInstance('payment_select')->getPaymentMethodFromFormElements($form['payment_method'], $form_state));
  }
}
