<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\ResponseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Response;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Url;
use Drupal\payment\Response\Response;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Response\Response
 *
 * @group Payment
 */
class ResponseUnitTest extends UnitTestCase {

  /**
   * The redirect URL.
   *
   * @var \Drupal\Core\Url
   */
  protected $redirectUrl;

  /**
   * The response.
   *
   * @var \Symfony\Component\HttpFoundation\Response|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $symfonyResponse;

  /**
   * The response under test.
   *
   * @var \Drupal\payment\Response\Response
   */
  protected $response;

  /**
   * The route name to test with.
   *
   * @var string
   */
  protected $routeName;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->routeName = $this->randomMachineName();

    $this->redirectUrl = new Url($this->routeName);

    $this->symfonyResponse = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
      ->disableOriginalConstructor()
      ->getMock();

    $this->response = new Response($this->redirectUrl, $this->symfonyResponse);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->response = new Response($this->redirectUrl, $this->symfonyResponse);
  }

  /**
   * @covers ::getRedirectUrl
   */
  function testGetRedirectUrl() {
    $this->assertSame($this->redirectUrl, $this->response->getRedirectUrl());
  }

  /**
   * @covers ::getResponse
   */
  function testGetResponse() {
    $this->assertSame($this->symfonyResponse, $this->response->getResponse());
  }

  /**
   * @covers ::getResponse
   */
  function testGetResponseWithoutResponse() {
    $url_generator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');
    $url_generator->expects($this->atLeastOnce())
      ->method('generateFromRoute')
      ->with($this->routeName)
      ->willReturn($this->randomMachineName());

    $container = new ContainerBuilder();
    $container->set('url_generator', $url_generator);

    \Drupal::setContainer($container);

    $this->response = new Response($this->redirectUrl);

    $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $this->response->getResponse());
  }

}
