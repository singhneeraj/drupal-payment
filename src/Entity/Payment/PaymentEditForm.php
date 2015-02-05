<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentEditForm.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\Entity\Currency;
use Drupal\currency\FormHelperInterface;
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
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();

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
  protected function copyFormValuesToEntity(EntityInterface $payment, array $form, FormStateInterface $form_state) {
    parent::copyFormValuesToEntity($payment, $form, $form_state);
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $values = $form_state->getValues();
    foreach (PaymentLineItemsInput::getLineItems($form['payment_line_items'], $form_state) as $line_item) {
      $payment->setLineItem($line_item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();

    $form_state->setRedirectUrl($payment->urlInfo());
  }

}
