<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentUI.
 */

namespace Drupal\payment;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for payment routes.
 */
class PaymentUI implements ContainerInjectionInterface {

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructor.
   */
  public function __construct(Request $request, UrlGeneratorInterface $url_generator) {
    $this->request = $request;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('request'), $container->get('url_generator'));
  }
}
