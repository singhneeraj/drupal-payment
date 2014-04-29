<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\PaymentMethod.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for payment routes.
 */
class Payment extends ControllerBase {

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
