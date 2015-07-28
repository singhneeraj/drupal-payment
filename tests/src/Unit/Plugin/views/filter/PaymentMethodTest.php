<?php

/**
 * @file Contains \Drupal\Tests\payment\Unit\Plugin\views\filter\PaymentMethodTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\views\filter;

use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\views\filter\PaymentMethod;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\views\filter\PaymentMethod
 *
 * @group Payment
 */
class PaymentMethodTest extends UnitTestCase {

  /**
   * The payment method manager.
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
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\views\filter\PaymentMethod
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentMethodManager = $this->getMock(PaymentMethodManagerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->sut = new PaymentMethod($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->paymentMethodManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $sut = PaymentMethod::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(PaymentMethod::class, $sut);
  }

  /**
   * @covers ::getValueOptions
   */
  public function testGetValueOptions() {
    $plugin_id_a = $this->randomMachineName();
    $plugin_label_a = $this->randomMachineName();
    $plugin_id_b = $this->randomMachineName();
    $plugin_label_b = $this->randomMachineName();
    $plugin_id_c = $this->randomMachineName();
    $plugin_label_c = $this->randomMachineName();

    $plugin_definitions = [
      $plugin_id_a => [
        'label' => $plugin_label_a,
      ],
      $plugin_id_b => [
        'label' => $plugin_label_b,
      ],
      $plugin_id_c => [
        'label' => $plugin_label_c,
      ],
    ];

    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn($plugin_definitions);

    $expected_options = [
      $plugin_id_a => $plugin_label_a,
      $plugin_id_b => $plugin_label_b,
      $plugin_id_c => $plugin_label_c,
    ];

    $this->assertSame($expected_options, $this->sut->getValueOptions());
  }

}
