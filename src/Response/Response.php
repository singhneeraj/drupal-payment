<?php

/**
 * @file
 * Contains \Drupal\payment\Response\Response.
 */

namespace Drupal\payment\Response;

use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Provides a payment response.
 */
class Response implements ResponseInterface {

  /**
   * The redirect URL.
   *
   * @var \Drupal\Core\Url
   */
  protected $url;

  /**
   * The response.
   *
   * @var \Symfony\Component\HttpFoundation\Response|null
   */
  protected $response;

  /**
   * Creates a new instance.
   *
   * @param \Drupal\Core\Url $url
   * @param \Symfony\Component\HttpFoundation\Response|null $response
   */
  public function __construct(Url $url, SymfonyResponse $response = NULL) {
    $this->response = $response;
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUrl() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    if (!$this->response) {
      $this->response = new TrustedRedirectResponse($this->url->toString());
    }

    return $this->response;
  }

}
