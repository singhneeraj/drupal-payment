<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Controller\PaymentReference.
 */

namespace Drupal\payment_reference\Controller;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Drupal\payment\QueueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for payment reference routes.
 */
class PaymentReference extends ControllerBase implements ContainerInjectionInterface, AccessInterface {

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
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface $payment_line_item_manager
   * @param \Drupal\payment\QueueInterface $queue
   */
  public function __construct(EntityManagerInterface $entity_manager, AccountInterface $current_user, EntityFormBuilderInterface $entity_form_builder, TranslationInterface $string_translation, PaymentLineItemManagerInterface $payment_line_item_manager, QueueInterface $queue) {
    $this->currentUser = $current_user;
    $this->entityManager = $entity_manager;
    $this->entityFormBuilder = $entity_form_builder;
    $this->paymentLineItemManager = $payment_line_item_manager;
    $this->queue = $queue;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('current_user'),
      $container->get('entity.form_builder'),
      $container->get('string_translation'),
      $container->get('plugin.manager.payment.line_item'),
      $container->get('payment_reference.queue')
    );
  }

  /**
   * Returns a payment page.
   *
   * @param string $entity_type_id
   *   The ID of the entity type the payment reference field is attached to.
   * @param string $bundle
   *   The bundle of the entity type the payment reference field is attached to.
   * @param string $field_name
   *   The name of the payment reference field.
   *
   * @return array
   *   A render array.
   */
  public function pay($entity_type_id, $bundle, $field_name) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->entityManager
      ->getStorage('payment')
      ->create(array(
        'bundle' => 'payment_reference',
      ));

    $field_definitions = $this->entityManager->getFieldDefinitions($entity_type_id, $bundle);
    $field_definition = $field_definitions[$field_name];
    $payment->setCurrencyCode($field_definition->getSetting('currency_code'));
    /** @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setEntityTypeId($entity_type_id);
    $payment_type->setBundle($bundle);
    $payment_type->setFieldName($field_name);
    foreach ($field_definition->getSetting('line_items_data') as $line_item_data) {
      $line_item = $this->paymentLineItemManager->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']);
      $payment->setLineItem($line_item);
    }

    return $this->entityFormBuilder->getForm($payment, 'payment_reference');
  }

  /**
   * Checks if the user has access to add a payment for a field instance.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param string $entity_type_id
   *   The ID of the entity type the payment reference field is attached to.
   * @param string $bundle
   *   The bundle of the entity type the payment reference field is attached to.
   * @param string $field_name
   *   The name of the payment reference field.
   *
   * @return string
   */
  public function payAccess(Request $request, $entity_type_id, $bundle, $field_name) {
    if ($this->fieldExists($entity_type_id, $bundle, $field_name)) {
      $field_definitions = $this->entityManager->getFieldDefinitions($entity_type_id, $bundle);
      $access_controller = $this->entityManager->getAccessControlHandler('payment');
      $payment_ids = $this->queue->loadPaymentIds($entity_type_id . '.' . $field_name, $this->currentUser->id());
      // Only grant access if the current user does not already have payments
      // available for this instance, and they have the permission to create them.
      if (empty($payment_ids) && $access_controller->createAccess('payment_reference') && $access_controller->fieldAccess('edit', $field_definitions[$field_name])) {
        return static::ALLOW;
      }
    }
    return static::DENY;
  }

  /**
   * Returns the label of a field.
   *
   * @param string $entity_type_id
   *   The ID of the entity type the payment reference field is attached to.
   * @param string $bundle
   *   The bundle of the entity type the payment reference field is attached to.
   * @param string $field_name
   *   The name of the payment reference field.
   *
   * @return string
   */
  public function payLabel($entity_type_id, $bundle, $field_name) {
    $field_definitions = $this->entityManager->getFieldDefinitions($entity_type_id, $bundle);

    return $field_definitions[$field_name]->getLabel();
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
    $field_definitions = $this->entityManager->getFieldDefinitions($payment_type->getEntityTypeId(), $payment_type->getBundle());
    $field_definition = $field_definitions[$payment_type->getFieldName()];

    return $field_definition->getLabel();
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
        '%status' => $payment->getPaymentStatus()->getLabel(),
      )) . ' ' . $message;
    }

    return array(
      '#type' => 'markup',
      '#markup' => $message,
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'payment_reference') . '/js/payment_reference.js'),
      ),
    );
  }

  /**
   * Checks if the user has access to resume a payment's context.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return string
   */
  public function resumeContextAccess(PaymentInterface $payment) {
    /** @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference $payment_type */
    $payment_type = $payment->getPaymentType();

    return $this->fieldExists($payment_type->getEntityTypeId(), $payment_type->getBundle(), $payment_type->getFieldName()) ? static::ALLOW : static::DENY;
  }

  /**
   * Checks if a field exists.
   *
   * @param string $entity_type_id
   *   The ID of the entity type the payment reference field is attached to.
   * @param string $bundle
   *   The bundle of the entity type the payment reference field is attached to.
   * @param string $field_name
   *   The name of the payment reference field.
   *
   * @return string
   */
  protected function fieldExists($entity_type_id, $bundle, $field_name) {
    if (!$this->entityManager->hasDefinition($entity_type_id)) {
      return FALSE;
    }
    $bundle_info = $this->entityManager->getBundleInfo($entity_type_id);
    if (!isset($bundle_info[$bundle])) {
      return FALSE;
    }
    $field_definitions = $this->entityManager->getFieldDefinitions($entity_type_id, $bundle);
    if (!isset($field_definitions[$field_name])) {
      return FALSE;
    }
    return TRUE;
  }

}
