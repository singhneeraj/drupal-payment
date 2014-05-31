<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentEditForm.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\currency\Entity\Currency;
use Drupal\payment\Element\PaymentLineItemsInput;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment edit form.
 */
class PaymentEditForm extends ContentEntityForm {

  /**
   * The payment line item plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   */
  protected $paymentLineItemManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface
   *   The payment line item manager.
   */
  function __construct(EntityManagerInterface $entity_manager, TranslationInterface $string_translation, PaymentLineItemManagerInterface $payment_line_item_manager) {
    parent::__construct($entity_manager);
    $this->paymentLineItemManager = $payment_line_item_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'), $container->get('string_translation'), $container->get('plugin.manager.payment.line_item'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();

    $form['payment_currency_code'] = array(
      '#type' => 'select',
      '#title' => $this->t('Currency'),
      '#options' => $this->currencyOptions(),
      '#default_value' => $payment->getCurrencyCode(),
      '#required' => TRUE,
    );
    $line_items_data = array();
    foreach ($payment->getLineItems() as $line_item) {
      $line_items_data[] = array(
        'plugin_id' => $line_item->getPluginId(),
        'plugin_configuration' => $line_item->getConfiguration(),
      );
    }
    $form['payment_line_items'] = array(
      '#type' => 'payment_line_items_input',
      '#title' => $this->t('Line items'),
      '#default_value' => $line_items_data,
      '#required' => TRUE,
      '#currency_code' => '',
    );

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $payment, array $form, array &$form_state) {
    parent::copyFormValuesToEntity($payment, $form, $form_state);
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $values = $form_state['values'];
    $payment->setCurrencyCode($values['payment_currency_code']);
    foreach (PaymentLineItemsInput::getLineItemsData($form['payment_line_items'], $form_state) as $line_item_data) {
      $line_item = $this->paymentLineItemManager->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']);
      $payment->setLineItem($line_item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    parent::save($form, $form_state);
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();
    $payment->save();

    $form_state['redirect_route'] = new Url('payment.payment.view', array(
      'payment' => $payment->id(),
    ));
  }

  /**
   * Wraps \Drupal\currency\Entity\Currency::options().
   *
   * @todo Revisit this when https://drupal.org/node/2118295 is fixed.
   */
  protected function currencyOptions() {
    return Currency::options();
  }
}
