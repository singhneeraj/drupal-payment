<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Status\BasicOperationsProviderUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\BasicOperationsProvider;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\BasicOperationsProvider
 *
 * @group Payment
 */
class BasicOperationsProviderUnitTest extends UnitTestCase {

  /**
   * The payment method configuration list builder.
   *
   * @var \Drupal\Core\Entity\EntityListBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationListBuilder;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * The provider under test
   *
   * @var \Drupal\payment\Plugin\Payment\Method\BasicOperationsProvider
   */
  protected $provider;

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $redirectDestination;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentMethodConfigurationListBuilder = $this->getMock('\Drupal\Core\Entity\EntityListBuilderInterface');

    $this->paymentMethodConfigurationStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->redirectDestination = $this->getMock('\Drupal\Core\Routing\RedirectDestinationInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->provider = new BasicOperationsProvider($this->stringTranslation, $this->redirectDestination, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationListBuilder);
  }

  /**
   * @covers ::getPaymentMethodConfiguration
   */
  public function testGetPaymentMethodConfiguration() {
    $entity_id = $this->randomMachineName();
    $plugin_id = 'payment_basic:' . $entity_id;

    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodConfigurationStorage->expects($this->once())
      ->method('load')
      ->with($entity_id)
      ->will($this->returnValue($payment_method_configuration));

    $method = new \ReflectionMethod($this->provider, 'getPaymentMethodConfiguration');
    $method->setAccessible(TRUE);
    $this->assertEquals($payment_method_configuration, $method->invoke($this->provider, $plugin_id));
  }

}
