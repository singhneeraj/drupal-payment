<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Controller\PaymentTypeUnitTest.
 */

namespace Drupal\payment\Tests\Controller;

use Drupal\payment\Controller\PaymentType;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Controller\PaymentType
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
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

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
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\Controller\PaymentType unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->formBuilder = $this->getMock('\Drupal\Core\Form\FormBuilderInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->paymentTypeManager= $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->controller = new PaymentType($this->moduleHandler, $this->entityManager, $this->formBuilder, $this->paymentTypeManager, $this->currentUser, $this->stringTranslation);
  }

  /**
   * @covers ::configure
   */
  public function testConfigure() {
    $bundle_exists = $this->randomName();
    $bundle_exists_definition = array(
      'configuration_form' => $this->randomName(),
    );
    $bundle_exists_no_form = $this->randomName();
    $bundle_exists_no_form_definition = array();
    $bundle_no_exists = $this->randomName();
    $bundle_no_exists_definition = NULL;

    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with($bundle_exists_definition['configuration_form'])
      ->will($this->returnValue(array()));

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
//    $this->assertInternalType('array', $build);

    // Test with a bundle of a plugin without a form.
//    $build = $this->controller->configure($bundle_exists_no_form);
//    $this->assertInternalType('string', $build);

    // Test with a non-existing bundle.
//    $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
//    $this->controller->configure($bundle_no_exists);
  }

}
