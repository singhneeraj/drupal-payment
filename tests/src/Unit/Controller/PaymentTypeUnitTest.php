<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\PaymentTypeUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\Url;
use Drupal\payment\Controller\PaymentType;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\PaymentType
 *
 * @group Payment
 */
class PaymentTypeUnitTest extends UnitTestCase {

  /**
   * The controller class under test.
   *
   * @var \Drupal\payment\Controller\PaymentType
   */
  protected $controller;

  /**
   * The current user used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The form builder used for testing.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formBuilder;

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

    $this->formBuilder = $this->getMock('\Drupal\Core\Form\FormBuilderInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->paymentTypeManager= $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->controller = new PaymentType($this->moduleHandler, $this->formBuilder, $this->paymentTypeManager, $this->currentUser, $this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->formBuilder),
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('plugin.manager.payment.type', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentTypeManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = PaymentType::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\PaymentType', $form);
  }

  /**
   * @covers ::configure
   */
  public function testConfigure() {
    $bundle_exists = $this->randomMachineName();
    $bundle_exists_definition = array(
      'configuration_form' => $this->randomMachineName(),
    );
    $bundle_exists_no_form = $this->randomMachineName();
    $bundle_exists_no_form_definition = array();
    $bundle_no_exists = $this->randomMachineName();
    $bundle_no_exists_definition = NULL;

    $form_build = array(
      '#type' => $this->randomMachineName(),
    );
    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with($bundle_exists_definition['configuration_form'])
      ->will($this->returnValue($form_build));

    $map = array(
      array($bundle_exists, FALSE, $bundle_exists_definition),
      array($bundle_exists_no_form, FALSE, $bundle_exists_no_form_definition),
      array($bundle_no_exists, FALSE, $bundle_no_exists_definition),
    );
    $this->paymentTypeManager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValueMap($map));

    // Test with a bundle of a plugin with a form.
    $build = $this->controller->configure($bundle_exists);
    $this->assertSame($form_build, $build);

    // Test with a bundle of a plugin without a form.
    $build = $this->controller->configure($bundle_exists_no_form);
    $this->assertInternalType('string', $build);

    // Test with a non-existing bundle.
    $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
    $this->controller->configure($bundle_no_exists);
  }

  /**
   * @covers ::configureTitle
   */
  public function testConfigureTitle() {
    $plugin_id = $this->randomMachineName();
    $label = $this->randomMachineName();
    $definition = array(
      'label' => $label,
    );

    $this->paymentTypeManager->expects($this->once())
      ->method('getDefinition')
      ->with($plugin_id)
      ->will($this->returnValue($definition));

    $this->assertSame($label, $this->controller->configureTitle($plugin_id));
  }

  /**
   * @covers ::listing
   */
  public function testListing() {
    $definitions = array(
      'foo' => array(
        'label' => $this->randomMachineName(),
        'description' => $this->randomMachineName(),
      ),
      'bar' => array(
        'label' => $this->randomMachineName(),
      ),
      'payment_unavailable' => array(),
    );

    $operations_foo = array(
      'baz' => array(
        'title' => $this->randomMachineName(),
      ),
    );

    $operations_provider_foo = $this->getMock('\Drupal\payment\Plugin\Payment\OperationsProviderInterface');
    $operations_provider_foo->expects($this->once())
      ->method('getOperations')
      ->with('foo')
      ->will($this->returnValue($operations_foo));

    $this->paymentTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $map = array(
      array('foo', $operations_provider_foo),
      array('bar', NULL),
    );
    $this->paymentTypeManager->expects($this->exactly(2))
      ->method('getOperationsProvider')
      ->will($this->returnValueMap($map));

    $this->moduleHandler->expects($this->any())
      ->method('moduleExists')
      ->with('field_ui')
      ->will($this->returnValue(TRUE));

    $map = array(
      array('administer payment fields', TRUE),
      array('administer payment form display', TRUE),
      array('administer payment display', TRUE),
    );
    $this->currentUser->expects($this->atLeastOnce())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));

    $build = $this->controller->listing();
    $expected_build = array(
      '#empty' => 'There are no available payment types.',
      '#header' => array('Type', 'Description', 'Operations'),
      '#type' => 'table',
      'foo' => array(
        'label' => array(
          '#markup' => $definitions['foo']['label'],
        ),
        'description' => array(
          '#markup' => $definitions['foo']['description'],
        ),
        'operations' => array(
          '#links' => $operations_foo + array(
            'configure' => array(
              'url' => new Url('payment.payment_type', array(
                'bundle' => 'foo',
              )),
              'title' => 'Configure',
            ),
            'manage-fields' => array(
              'title' => 'Manage fields',
              'url' => new Url('field_ui.overview_payment', array(
                'bundle' => 'foo',
              )),
            ),
            'manage-form-display' => array(
              'title' => 'Manage form display',
              'url' => new Url('field_ui.form_display_overview_payment', array(
                'bundle' => 'foo',
              )),
            ),
            'manage-display' => array(
              'title' => 'Manage display',
              'url' => new Url('field_ui.display_overview_payment', array(
                'bundle' => 'foo',
              )),
            ),
          ),
          '#type' => 'operations',
        ),
      ),
      'bar' => array(
        'label' => array(
          '#markup' => $definitions['bar']['label'],
        ),
        'description' => array(
          '#markup' => NULL,
        ),
        'operations' => array(
          '#links' => array(
            'configure' => array(
              'url' => new Url('payment.payment_type', array(
                'bundle' => 'bar',
              )),
              'title' => 'Configure',
            ),
            'manage-fields' => array(
              'title' => 'Manage fields',
              'url' => new Url('field_ui.overview_payment', array(
                'bundle' => 'bar',
              )),
            ),
            'manage-form-display' => array(
              'title' => 'Manage form display',
              'url' => new Url('field_ui.form_display_overview_payment', array(
                'bundle' => 'bar',
              )),
            ),
            'manage-display' => array(
              'title' => 'Manage display',
              'url' => new Url('field_ui.display_overview_payment', array(
                'bundle' => 'bar',
              )),
            ),
          ),
          '#type' => 'operations',
        ),
      ),
    );
    $this->assertEquals($expected_build, $build);
  }

}
