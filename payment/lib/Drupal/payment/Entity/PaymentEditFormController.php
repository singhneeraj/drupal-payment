<?php

/**
 * @file
 * Contains Drupal\payment\Entity\PaymentEditFormController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityFormController;
use Drupal\payment\Element\PaymentMethodInput;
use Drupal\payment\Plugin\Payment\Status\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment edit form.
 */
class PaymentEditFormController extends EntityFormController {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  protected $paymentStatusManager;

  /**
   * Constructor.
   *
   * @param \Drupal\payment\Plugin\Payment\Status\Manager The payment status
   *   plugin manager.
   */
  function __construct(Manager $payment_status_manager) {
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $payment = $this->getEntity();
    $form['status_plugin_id'] = array(
      '#default_value' => $payment->getStatus()->getPluginId(),
      '#description' => t('Updating a payment status manually can disrupt automatic payment processing.'),
      '#options' => $this->paymentStatusManager->options(),
      '#title' => t('Status'),
      '#type' => 'select',
    );

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    parent::submit($form, $form_state);
    $payment = $this->getEntity();
    $payment->save();
    $uri = $payment->uri();
    $form_state['redirect'] = $uri['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    $payment = $this->getEntity();
    if ($form_state['values']['status_plugin_id'] != $payment->getStatus()->getPluginId()) {
      $status = $this->paymentStatusManager
        ->createInstance($form_state['values']['status_plugin_id']);
      $payment->setStatus($status);
    }

    return $payment;
  }
}
