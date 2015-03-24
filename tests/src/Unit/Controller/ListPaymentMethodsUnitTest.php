<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\ListPaymentMethodsUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\ListPaymentMethods;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\ListPaymentMethods
 *
 * @group Payment
 */
class ListPaymentMethodsUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\ListPaymentMethods
   */
  protected $controller;

  /**
   * The payment method plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new ListPaymentMethods($this->stringTranslation, $this->paymentMethodManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = ListPaymentMethods::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\ListPaymentMethods', $form);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $plugin_id_a = $this->randomMachineName();
    $plugin_id_b = $this->randomMachineName();
    $definitions = [
      $plugin_id_a => [
        'active' => TRUE,
        'class' => $this->getMockClass('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface'),
        'label' => $this->randomMachineName(),
      ],
      $plugin_id_b => [
        'active' => FALSE,
        'class' => $this->getMockClass('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface'),
        'label' => $this->randomMachineName(),
      ],
    ];

    $this->paymentMethodManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $build = $this->controller->execute();
    $this->assertInternalType('array', $build);
  }

}
