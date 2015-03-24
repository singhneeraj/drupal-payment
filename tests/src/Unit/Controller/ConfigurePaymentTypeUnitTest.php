<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\ConfigurePaymentTypeUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\ConfigurePaymentType;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\ConfigurePaymentType
 *
 * @group Payment
 */
class ConfigurePaymentTypeUnitTest extends UnitTestCase {

  /**
   * The controller class under test.
   *
   * @var \Drupal\payment\Controller\ConfigurePaymentType
   */
  protected $controller;

  /**
   * The form builder used for testing.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formBuilder;

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
   */
  protected function setUp() {
    $this->formBuilder = $this->getMock('\Drupal\Core\Form\FormBuilderInterface');

    $this->paymentTypeManager= $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new ConfigurePaymentType($this->formBuilder, $this->paymentTypeManager, $this->stringTranslation);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->formBuilder],
      ['plugin.manager.payment.type', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentTypeManager],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = ConfigurePaymentType::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\ConfigurePaymentType', $form);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $bundle_exists = $this->randomMachineName();
    $bundle_exists_definition = [
      'configuration_form' => $this->randomMachineName(),
    ];
    $bundle_exists_no_form = $this->randomMachineName();
    $bundle_exists_no_form_definition = [];
    $bundle_no_exists = $this->randomMachineName();
    $bundle_no_exists_definition = NULL;

    $form_build = [
      '#type' => $this->randomMachineName(),
    ];
    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with($bundle_exists_definition['configuration_form'])
      ->will($this->returnValue($form_build));

    $map = [
      [$bundle_exists, FALSE, $bundle_exists_definition],
      [$bundle_exists_no_form, FALSE, $bundle_exists_no_form_definition],
      [$bundle_no_exists, FALSE, $bundle_no_exists_definition],
    ];
    $this->paymentTypeManager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValueMap($map));

    // Test with a bundle of a plugin with a form.
    $build = $this->controller->execute($bundle_exists);
    $this->assertSame($form_build, $build);

    // Test with a bundle of a plugin without a form.
    $build = $this->controller->execute($bundle_exists_no_form);
    $this->assertInternalType('array', $build);

    // Test with a non-existing bundle.
    $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
    $this->controller->execute($bundle_no_exists);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $plugin_id = $this->randomMachineName();
    $label = $this->randomMachineName();
    $definition = [
      'label' => $label,
    ];

    $this->paymentTypeManager->expects($this->once())
      ->method('getDefinition')
      ->with($plugin_id)
      ->will($this->returnValue($definition));

    $this->assertSame($label, $this->controller->title($plugin_id));
  }

}
