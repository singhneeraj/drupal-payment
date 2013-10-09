<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentMethodElement.
 */

namespace Drupal\payment_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Form\FormInterface;
use Drupal\payment\Element\PaymentPaymentMethodInput;
use Drupal\payment\Generate;
use Drupal\payment\Plugin\payment\type\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentMethodElement implements ContainerInjectionInterface, FormInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructor.
   */
  function __construct(EntityManager $entity_manager, Manager $type_manager) {
    $this->entityManager = $entity_manager;
    $this->typeManager = $type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.type'));
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
    $payment = $this->entityManager->getStorageController('payment')->create(array(
      'bundle' => 'payment_unavailable',
    ))->setLineItems(Generate::createPaymentLineItems());
    $form['payment_method'] = array(
      '#default_value' => $payment,
      '#required' => TRUE,
      '#type' => 'payment_payment_method_input',
    );
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
    $value = \Drupal::state()->set('payment_test_method_form_element', PaymentPaymentMethodInput::getPayment($form['payment_method'], $form_state)->getPaymentMethodId());
  }
}
