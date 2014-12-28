<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\EditPaymentMethodConfigurationUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\EditPaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\EditPaymentMethodConfiguration
 *
 * @group Payment
 */
class EditPaymentMethodConfigurationUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\EditPaymentMethodConfiguration
   */
  protected $controller;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new EditPaymentMethodConfiguration($this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = EditPaymentMethodConfiguration::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\EditPaymentMethodConfiguration', $form);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method_configuration->expects($this->once())
      ->method('label');

    $this->assertInternalType('string', $this->controller->title($payment_method_configuration));
  }

}
