<?php

/**
 * @file Contains \Drupal\Tests\payment\Unit\Plugin\views\filter\PaymentStatusTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\views\filter;

use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Plugin\views\filter\PaymentStatus;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\views\filter\PaymentStatus
 *
 * @group Payment
 */
class PaymentStatusTest extends UnitTestCase {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\views\filter\PaymentStatus
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentStatusManager = $this->getMock(PaymentStatusManagerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->sut = new PaymentStatus($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $sut = PaymentStatus::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(PaymentStatus::class, $sut);
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

    $this->paymentStatusManager->expects($this->atLeastOnce())
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
