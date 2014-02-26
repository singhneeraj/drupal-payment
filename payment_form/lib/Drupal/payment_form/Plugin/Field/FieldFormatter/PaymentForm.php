<?php

/**
 * @file
 * Contains \Drupal\payment_form\Plugin\field\formatter\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A payment form formatter.
 *
 * @FieldFormatter(
 *   id = "payment_form",
 *   label = @Translation("Payment form"),
 *   field_types = {
 *     "payment_form",
 *   }
 * )
 */
class PaymentForm extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   */
  protected $paymentLineItemManager;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   * @param array $plugin_id
   * @param array $plugin_definition
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface $payment_line_item_manager
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, Request $request, EntityManagerInterface $entity_manager, FormBuilderInterface $form_builder, PaymentLineItemManagerInterface $payment_line_item_manager) {
    parent::__construct($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode']);
    $this->entityManager = $entity_manager;
    $this->formBuilder = $form_builder;
    $this->paymentLineItemManager = $payment_line_item_manager;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('request'), $container->get('entity.manager'), $container->get('form_builder'), $container->get('plugin.manager.payment.line_item'));
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->entityManager->getStorageController('payment')->create(array(
      'bundle' => 'payment_form',
    ));
    $payment->setCurrencyCode($this->fieldDefinition->getSetting('currency_code'));
    /** @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setDestinationUrl($this->request->getUri());
    foreach ($items as $item) {
      /** @var \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm $item */
      $plugin_id = $item->get('plugin_id')->getValue();
      if ($plugin_id) {
        $payment->setLineItem($this->paymentLineItemManager->createInstance($plugin_id, $item->get('plugin_configuration')->getValue()));
      }
    }
    /** @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm $payment_type */
    $payment_type = $payment->getPaymentType();
    $payment_type->setFieldInstanceConfigId($this->fieldDefinition->getName());

    return $this->formBuilder->getForm($this->entityManager->getFormController('payment', 'payment_form')->setEntity($payment));
  }

}
