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
   */
  function __construct(EntityManagerInterface $entity_manager, TranslationInterface $string_translation) {
    parent::__construct($entity_manager);
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'), $container->get('string_translation'));
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
    $form['payment_line_items'] = array(
      '#type' => 'payment_line_items_input',
      '#title' => $this->t('Line items'),
      '#default_value' => $payment->getLineItems(),
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
    foreach (PaymentLineItemsInput::getLineItems($form['payment_line_items'], $form_state) as $line_item) {
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
