<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm.
 */

namespace Drupal\payment\Entity\PaymentStatus;

use Drupal\Core\Entity\EntityForm;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment status form.
 */
class PaymentStatusForm extends EntityForm {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PaymentStatusManagerInterface $payment_status_manager) {
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
    /** @var \Drupal\payment\Entity\PaymentStatusInterface $payment_status */
    $payment_status = $this->getEntity();
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => $payment_status->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#default_value' => $payment_status->id(),
      '#disabled' => (bool) $payment_status->id(),
      '#machine_name' => array(
        'source' => array('label'),
        'exists' => array($this, 'PaymentStatusIdExists'),
      ),
      '#maxlength' => 255,
      '#type' => 'machine_name',
      '#required' => TRUE,
    );
    $form['parent_id'] = array(
      '#default_value' => $payment_status->getParentId(),
      '#empty_value' => '',
      '#options' => $this->paymentStatusManager->options(),
      '#title' => t('Parent status'),
      '#type' => 'select',
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#default_value' => $payment_status->getDescription(),
      '#maxlength' => 255,
    );

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    parent::submit($form, $form_state);
    $values = $form_state['values'];
    /** @var \Drupal\payment\Entity\PaymentStatusInterface $status */
    $status = $this->getEntity();
    $status->setId($values['id'])
      ->setLabel($values['label'])
      ->setParentId($values['parent_id'])
      ->setDescription($values['description']);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $payment_status = $this->getEntity();
    $payment_status->save();
    drupal_set_message(t('@label has been saved.', array(
      '@label' => $payment_status->label()
    )));
    $form_state['redirect_route'] = array(
      'route_name' => 'payment.payment_status.list',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $form_state['redirect'] = array('admin/config/services/payment/status/delete/' . $this->getEntity()->id());
  }

  /**
   * Checks if a payment method with a particular ID already exists.
   *
   * @param string $id
   *
   * @return bool
   */
  function paymentStatusIdExists($id) {
    return (bool) entity_load('payment_status', $id);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    if (!$this->getEntity()->id()) {
      unset($actions['delete']);
    }

    return $actions;
  }
}
