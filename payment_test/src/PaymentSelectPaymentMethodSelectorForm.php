<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentSelectPaymentMethodSelectorForm.
 */

namespace Drupal\payment_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\payment\Tests\Generate;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to test the payment_select payment method selector plugin.
 */
class PaymentSelectPaymentMethodSelectorForm implements ContainerInjectionInterface, FormInterface {

  use DependencySerializationTrait;

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
   * Constructs a new class instance.
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
  public function getFormId() {
    return 'payment_test_payment_method_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tree = FALSE) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->entityManager->getStorage('payment')->create(array(
      'bundle' => 'payment_unavailable',
    ));
    $payment->setLineItems(Generate::createPaymentLineItems());
    if (isset($form_state['storage']['payment_method_selector'])) {
      $payment_method_selector = $form_state['storage']['payment_method_selector'];
    }
    else {
      $payment_method_selector = $this->paymentMethodSelectorManager->createInstance('payment_select');
      $payment_method_selector->setPayment($payment);
      $payment_method_selector->setRequired();
      $form_state['storage']['payment_method_selector'] = $payment_method_selector;
    }

    $form['payment_method'] = $payment_method_selector->buildConfigurationForm(array(), $form_state);
    // Nest the selector in a tree if that's required.
    if ($tree) {
      $form['tree'] = array(
        '#tree' => TRUE,
      );
      $form['tree']['payment_method'] = $form['payment_method'];
      unset($form['payment_method']);

    }
    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface $payment_method_selector */
    $payment_method_selector = $form_state['storage']['payment_method_selector'];
    $plugin_form = isset($form['tree']) ? $form['tree']['payment_method'] : $form['payment_method'];
    $payment_method_selector->validateConfigurationForm($plugin_form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface $payment_method_selector */
    $payment_method_selector = $form_state['storage']['payment_method_selector'];
    $plugin_form = isset($form['tree']) ? $form['tree']['payment_method'] : $form['payment_method'];
    $payment_method_selector->submitConfigurationForm($plugin_form, $form_state);
    \Drupal::state()->set('payment_test_method_form_element', $payment_method_selector->getPaymentMethod());
  }
}
