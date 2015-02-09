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
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->controller = new DisablePaymentMethodConfiguration();
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $url = 'http://example.com/' . $this->randomMachineName();

    $payment_method_configuration = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('disable');
    $payment_method_configuration->expects($this->atLeastOnce())
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
