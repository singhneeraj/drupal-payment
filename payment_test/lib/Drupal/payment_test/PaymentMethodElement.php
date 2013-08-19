<?php

/**
 * @file
 * Contains \Drupal\payment_test\PaymentMethodElement.
 */

namespace Drupal\payment_test;

use Drupal\Core\Controller\ControllerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Form\FormInterface;
use Drupal\payment\Element\PaymentMethod;
use Drupal\payment\Generate;
use Drupal\payment\Plugin\payment\context\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentMethodElement implements ControllerInterface, FormInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\payment\Plugin\payment\context\Manager
   */
  protected $contextManager;

  /**
   * Constructor.
   */
  function __construct(EntityManager $entity_manager, Manager $context_manager) {
    $this->entityManager = $entity_manager;
    $this->contextManager = $context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.context'));
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
      '#type' => 'payment_method',
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
    $value = \Drupal::state()->set('payment_test_method_form_element', PaymentMethod::getPayment($form['payment_method'], $form_state)->getPaymentMethodId());
  }
}
