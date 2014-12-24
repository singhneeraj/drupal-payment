<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\DisablePaymentMethodConfigurationUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\DisablePaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
 * @coversDefaultClass \Drupal\payment\Controller\DisablePaymentMethodConfiguration
 *
 * @group Payment
 */
class DisablePaymentMethodConfigurationUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\DisablePaymentMethodConfiguration
   */
  protected $controller;

  /**
   * The URL generator used for testing.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {

    $this->urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');

    $this->controller = new DisablePaymentMethodConfiguration($this->urlGenerator);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['url_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = DisablePaymentMethodConfiguration::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\DisablePaymentMethodConfiguration', $form);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $this->urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');
    $payment_method->expects($this->once())
      ->method('disable');
    $payment_method->expects($this->once())
      ->method('save');
    $this->controller->execute($payment_method);
  }

}
