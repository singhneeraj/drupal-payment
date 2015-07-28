<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\ResponseTest.
 */

namespace Drupal\Tests\payment\Unit\Response;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\payment\Response\Response;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @coversDefaultClass \Drupal\payment\Response\Response
 *
 * @group Payment
 */
class ResponseTest extends UnitTestCase {

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
   * The route name to test with.
   *
   * @var string
   */
  protected $routeName;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Response\Response
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->routeName = $this->randomMachineName();

    $this->redirectUrl = new Url($this->routeName);

    $this->symfonyResponse = $this->getMockBuilder(SymfonyResponse::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->sut = new Response($this->redirectUrl, $this->symfonyResponse);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new Response($this->redirectUrl, $this->symfonyResponse);
  }

  /**
   * @covers ::getRedirectUrl
   */
  function testGetRedirectUrl() {
    $this->assertSame($this->redirectUrl, $this->sut->getRedirectUrl());
  }

  /**
   * @covers ::getResponse
   */
  function testGetResponse() {
    $this->assertSame($this->symfonyResponse, $this->sut->getResponse());
  }

  /**
   * @covers ::getResponse
   */
  function testGetResponseWithoutResponse() {
    $url_generator = $this->getMock(UrlGeneratorInterface::class);
    $url_generator->expects($this->atLeastOnce())
      ->method('generateFromRoute')
      ->with($this->routeName)
      ->willReturn($this->randomMachineName());

    $container = new ContainerBuilder();
    $container->set('url_generator', $url_generator);

    \Drupal::setContainer($container);

    $this->sut = new Response($this->redirectUrl);

    $this->assertInstanceOf(SymfonyResponse::class, $this->sut->getResponse());
  }

}
