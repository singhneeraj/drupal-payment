<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentEditForm.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment edit form.
 */
class PaymentEditForm extends EntityForm {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface The payment status
   *   plugin manager.
   */
  function __construct(PaymentStatusManagerInterface $payment_status_manager) {
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
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
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
    $form_state['redirect_route'] = array(
      'route_name' => 'payment.payment.view',
      'route_parameters' => array(
        'payment' => $payment->id(),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();
    if ($form_state['values']['status_plugin_id'] != $payment->getStatus()->getPluginId()) {
      $status = $this->paymentStatusManager
        ->createInstance($form_state['values']['status_plugin_id']);
      $payment->setStatus($status);
    }

    return $payment;
  }
}
