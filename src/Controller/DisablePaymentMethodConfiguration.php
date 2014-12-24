<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\DisablePaymentMethodConfiguration.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Handles the "disable payment method configuration" route.
 */
class DisablePaymentMethodConfiguration extends ControllerBase {

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   */
  public function __construct(UrlGeneratorInterface $url_generator) {
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('url_generator'));
  }

  /**
   * Disables a payment method configuration.
   *
   * @param \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method_configuration
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function execute(PaymentMethodConfigurationInterface $payment_method_configuration) {
    $payment_method_configuration->disable();
    $payment_method_configuration->save();

    return new RedirectResponse($this->urlGenerator->generateFromRoute('payment.payment_method_configuration.list', [], [
      'absolute' => TRUE,
    ]));
  }

}
