<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentStatus\PaymentStatusForm.
 */

namespace Drupal\payment\Entity\PaymentStatus;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment status add/edit form.
 */
class PaymentStatusForm extends EntityForm {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The payment status storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentStatusStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(TranslationInterface $string_translation, EntityStorageInterface $payment_status_storage, PaymentStatusManagerInterface $payment_status_manager) {
    $this->paymentStatusManager = $payment_status_manager;
    $this->paymentStatusStorage = $payment_status_storage;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($container->get('string_translation'), $entity_manager->getStorage('payment_status'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Entity\PaymentStatusInterface $payment_status */
    $payment_status = $this->getEntity();
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $payment_status->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#default_value' => $payment_status->id(),
      '#disabled' => !$payment_status->isNew(),
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
      '#title' => $this->t('Parent status'),
      '#type' => 'select',
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $payment_status->getDescription(),
      '#maxlength' => 255,
    );

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $payment_status, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Entity\PaymentStatusInterface $payment_status */
    parent::copyFormValuesToEntity($payment_status, $form, $form_state);
    $values = $form_state['values'];
    $payment_status->setId($values['id']);
    $payment_status->setLabel($values['label']);
    $payment_status->setParentId($values['parent_id']);
    $payment_status->setDescription($values['description']);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $payment_status = $this->getEntity();
    $payment_status->save();
    drupal_set_message($this->t('@label has been saved.', array(
      '@label' => $payment_status->label()
    )));
    $form_state->setRedirect('payment.payment_status.list');
  }

  /**
   * Checks if a payment status with a particular ID already exists.
   *
   * @param string $id
   *
   * @return bool
   */
  public function paymentStatusIdExists($id) {
    return (bool) $this->paymentStatusStorage->load($id);
  }
}
