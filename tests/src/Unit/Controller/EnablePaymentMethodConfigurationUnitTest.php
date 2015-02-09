<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\EnablePaymentMethodConfigurationUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\EnablePaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\EnablePaymentMethodConfiguration
 *
 * @group Payment
 */
class EnablePaymentMethodConfigurationUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\EnablePaymentMethodConfiguration
   */
  protected $controller;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->controller = new EnablePaymentMethodConfiguration();
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $url = 'http://example.com/' . $this->randomMachineName();

    $payment_method_configuration = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');
    $payment_method_configuration->expects($this->once())
      ->method('enable');
    $payment_method_configuration->expects($this->once())
      ->method('save');
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('url')
      ->with('collection')
      ->willReturn($url);

    $response = $this->controller->execute($payment_method_configuration);
    $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
    $this->assertSame($url, $response->getTargetUrl());
  }

}
