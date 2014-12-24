<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\ListPaymentTypesUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\Url;
use Drupal\payment\Controller\ListPaymentTypes;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\ListPaymentTypes
 *
 * @group Payment
 */
class ListPaymentTypesUnitTest extends UnitTestCase {

  /**
   * The controller class under test.
   *
   * @var \Drupal\payment\Controller\ListPaymentTypes
   */
  protected $controller;

  /**
   * The current user used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The payment type plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentTypeManager;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->paymentTypeManager= $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new ListPaymentTypes($this->moduleHandler, $this->paymentTypeManager, $this->currentUser, $this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser],
      ['module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler],
      ['plugin.manager.payment.type', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentTypeManager],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = ListPaymentTypes::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\ListPaymentTypes', $form);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $definitions = [
      'foo' => [
        'label' => $this->randomMachineName(),
        'description' => $this->randomMachineName(),
      ],
      'bar' => [
        'label' => $this->randomMachineName(),
      ],
      'payment_unavailable' => [],
    ];

    $operations_foo = [
      'baz' => [
        'title' => $this->randomMachineName(),
      ],
    ];

    $operations_provider_foo = $this->getMock('\Drupal\payment\Plugin\Payment\OperationsProviderInterface');
    $operations_provider_foo->expects($this->once())
      ->method('getOperations')
      ->with('foo')
      ->will($this->returnValue($operations_foo));

    $this->paymentTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $map = [
      ['foo', $operations_provider_foo],
      ['bar', NULL],
    ];
    $this->paymentTypeManager->expects($this->exactly(2))
      ->method('getOperationsProvider')
      ->will($this->returnValueMap($map));

    $this->moduleHandler->expects($this->any())
      ->method('moduleExists')
      ->with('field_ui')
      ->will($this->returnValue(TRUE));

    $map = [
      ['administer payment fields', TRUE],
      ['administer payment form display', TRUE],
      ['administer payment display', TRUE],
    ];
    $this->currentUser->expects($this->atLeastOnce())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));

    $build = $this->controller->execute();
    $expected_build = [
      '#empty' => 'There are no available payment types.',
      '#header' => ['Type', 'Description', 'Operations'],
      '#type' => 'table',
      'foo' => [
        'label' => [
          '#markup' => $definitions['foo']['label'],
        ],
        'description' => [
          '#markup' => $definitions['foo']['description'],
        ],
        'operations' => [
          '#links' => $operations_foo + [
            'configure' => [
              'url' => new Url('payment.payment_type', [
                'bundle' => 'foo',
              ]),
              'title' => 'Configure',
            ],
            'manage-fields' => [
              'title' => 'Manage fields',
              'url' => new Url('field_ui.overview_payment', [
                'bundle' => 'foo',
              ]),
            ],
            'manage-form-display' => [
              'title' => 'Manage form display',
              'url' => new Url('field_ui.form_display_overview_payment', [
                'bundle' => 'foo',
              ]),
            ],
            'manage-display' => [
              'title' => 'Manage display',
              'url' => new Url('field_ui.display_overview_payment', [
                'bundle' => 'foo',
              ]),
            ],
          ],
          '#type' => 'operations',
        ],
      ],
      'bar' => [
        'label' => [
          '#markup' => $definitions['bar']['label'],
        ],
        'description' => [
          '#markup' => NULL,
        ],
        'operations' => [
          '#links' => [
            'configure' => [
              'url' => new Url('payment.payment_type', [
                'bundle' => 'bar',
              ]),
              'title' => 'Configure',
            ],
            'manage-fields' => [
              'title' => 'Manage fields',
              'url' => new Url('field_ui.overview_payment', [
                'bundle' => 'bar',
              ]),
            ],
            'manage-form-display' => [
              'title' => 'Manage form display',
              'url' => new Url('field_ui.form_display_overview_payment', [
                'bundle' => 'bar',
              ]),
            ],
            'manage-display' => [
              'title' => 'Manage display',
              'url' => new Url('field_ui.display_overview_payment', [
                'bundle' => 'bar',
              ]),
            ],
          ],
          '#type' => 'operations',
        ],
      ],
    ];
    $this->assertEquals($expected_build, $build);
  }

}
