<?php

/**
 * @file
 * Contains \Drupal\payment_reference\PaymentReference\Controller.
 */

namespace Drupal\payment_reference\Controller;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\field\FieldInstanceConfigInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface as PaymentLineItemManager;
use Drupal\payment\QueueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for payment reference routes.
 */
class PaymentReference extends ControllerBase implements ContainerInjectionInterface, AccessInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   */
  protected $paymentLineItemManager;

  /**
   * The payment reference queue.
   *
   * @var \Drupal\payment\QueueInterface
   */
  protected $queue;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface $payment_line_item_manager
   * @param \Drupal\payment\QueueInterface $queue
   */
  public function __construct(FormBuilder $form_builder, PaymentLineItemManager $payment_line_item_manager, QueueInterface $queue) {
    $this->formBuilder = $form_builder;
    $this->paymentLineItemManager = $payment_line_item_manager;
    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('plugin.manager.payment.line_item'),
      $container->get('payment_reference.queue')
    );
  }

  /**
   * Returns a payment page.
   *
   * @param \Drupal\field\FieldInstanceConfigInterface $field_instance_config
   *
   * @return array
   *   A render array.
   */
  public function pay(FieldInstanceConfigInterface $field_instance_config) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->entityManager()
      ->getStorage('payment')
      ->create(array(
        'bundle' => 'payment_reference',
      ));
    $payment->setCurrencyCode($field_instance_config->getSetting('currency_code'));
    /** @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setFieldInstanceConfigId($field_instance_config->id());
    foreach ($field_instance_config->getSetting('line_items_data') as $line_item_data) {
      $line_item = $this->paymentLineItemManager->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']);
      $payment->setLineItem($line_item);
    }
    $form = $this->entityManager()->getFormObject('payment', 'payment_reference')->setEntity($payment);

    return $this->formBuilder->getForm($form);
  }

  /**
   * Checks if the user has access to add a payment for a field instance.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\field\FieldInstanceConfigInterface $field_instance_config
   *
   * @return string
   */
  public function payAccess(Request $request, FieldInstanceConfigInterface $field_instance_config) {
    $access_controller = $this->entityManager()->getAccessController('payment');
    $payment_ids = $this->queue->loadPaymentIds($field_instance_config->id(), $this->currentUser()->id());
    // Only grant access if the current user does not already have payments
    // available for this instance, and he has the permission to create them.
    if ($access_controller->createAccess('payment_reference') && empty($payment_ids)) {
      return static::ALLOW;
    }
    return static::DENY;
  }

  /**
   * Returns the label of a field instance.
   *
   * @param \Drupal\field\FieldInstanceConfigInterface $field_instance_config
   *
   * @return string
   */
  public function payLabel(FieldInstanceConfigInterface $field_instance_config) {
    return $field_instance_config->label();
  }

  /**
   * Returns the label of a field instance.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return string
   */
  public function resumeContextLabel(PaymentInterface $payment) {
    /** @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference $payment_type */
    $payment_type = $payment->getPaymentType();
    $field_instance_config_storage = $this->entityManager()->getStorage('field_instance');
    $field_instance_config = $field_instance_config_storage->load($payment_type->getFieldInstanceConfigId());

    return $field_instance_config->label();
  }

  /**
   * Resumes the payment context.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return array
   *   A renderable array.
   */
  public function resumeContext(PaymentInterface $payment) {
    $message = $this->t('You can now <span class="payment_reference-window-close">close this window</span>.');
    if ($payment->access('view')) {
      $message = $this->t('Your payment is %status.', array(
        '%status' => $payment->getStatus()->getLabel(),
      )) . ' ' . $message;
    }

    return array(
      '#type' => 'markup',
      '#markup' => $message,
      '#attached' => array(
        'js' => array($this->drupalGetPath('module', 'payment_reference') . '/js/payment_reference.js'),
      ),
    );
  }

  /**
   * Wraps drupal_get_path().
   */
  protected function drupalGetPath($type, $name) {
    return drupal_get_path($type, $name);
  }
}