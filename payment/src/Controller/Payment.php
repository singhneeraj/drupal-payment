<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\PaymentMethod.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment routes.
 */
class Payment extends ControllerBase {

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'));
  }

  /**
   * Returns the title for the payment view page.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return string
   */
  public function viewTitle(PaymentInterface $payment) {
    return $this->t('Payment #!payment_id', array(
      '!payment_id' => $payment->id(),
    ));
  }

  /**
   * Returns the title for the payment edit page.
   *
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *
   * @return string
   */
  public function editTitle(PaymentInterface $payment) {
    return $this->t('Edit payment #!payment_id', array(
      '!payment_id' => $payment->id(),
    ));
  }
}
