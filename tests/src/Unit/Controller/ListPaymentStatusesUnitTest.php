<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\ListPaymentStatusesUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\ListPaymentStatuses;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\ListPaymentStatuses
 *
 * @group Payment
 */
class ListPaymentStatusesUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\ListPaymentStatuses
   */
  protected $controller;

  /**
   * The payment method plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

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
    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->renderer = $this->getMock('\Drupal\Core\Render\RendererInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new ListPaymentStatuses($this->stringTranslation, $this->renderer, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager],
      ['renderer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->renderer],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = ListPaymentStatuses::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\ListPaymentStatuses', $form);
  }

  /**
   * @covers ::execute
   * @covers ::buildListingLevel
   */
  function testListing() {
    $plugin_id_a = $this->randomMachineName();
    $plugin_id_b = $this->randomMachineName();

    $definitions = [
      $plugin_id_a => [
        'label' => $this->randomMachineName(),
        'description' => $this->randomMachineName(),
      ],
      $plugin_id_b => [
        'label' => $this->randomMachineName(),
        'description' => $this->randomMachineName(),
      ],
    ];

    $operations_a = [
      'title' => $this->randomMachineName(),
    ];

    $operations_provider_a = $this->getMock('\Drupal\payment\Plugin\Payment\OperationsProviderInterface');
    $operations_provider_a->expects($this->once())
      ->method('getOperations')
      ->with($plugin_id_a)
      ->will($this->returnValue($operations_a));

    $map = [
      [$plugin_id_a, TRUE, $definitions[$plugin_id_a]],
      [$plugin_id_b, TRUE, $definitions[$plugin_id_b]],
    ];
    $this->paymentStatusManager->expects($this->exactly(2))
      ->method('getDefinition')
      ->will($this->returnValueMap($map));
    $map = [
      [$plugin_id_a, $operations_provider_a],
      [$plugin_id_b, NULL],
    ];
    $this->paymentStatusManager->expects($this->exactly(2))
      ->method('getOperationsProvider')
      ->will($this->returnValueMap($map));

    $hierarchy = [
      $plugin_id_a => [
        $plugin_id_b => [],
      ],
    ];

    $this->paymentStatusManager->expects($this->once())
      ->method('hierarchy')
      ->will($this->returnValue($hierarchy));

    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $build = $this->controller->execute();
    $expected = [
      '#header' => ['Title', 'Description', 'Operations'],
      '#type' => 'table',
      $plugin_id_a => [
        'label' => [
          '#markup' => $definitions[$plugin_id_a]['label'],
        ],
        'description' => [
          '#markup' => $definitions[$plugin_id_a]['description'],
        ],
        'operations' => [
          '#type' => 'operations',
          '#links' => $operations_a,
        ],
      ],
      $plugin_id_b => [
        'label' => [
          '#markup' => $definitions[$plugin_id_b]['label'],
        ],
        'description' => [
          '#markup' => $definitions[$plugin_id_b]['description'],
        ],
        'operations' => [
          '#type' => 'operations',
          '#links' => [],
        ],
      ],
    ];
    $this->assertSame($expected, $build);
  }

}
