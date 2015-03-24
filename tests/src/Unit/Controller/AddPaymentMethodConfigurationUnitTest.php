<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\AddPaymentMethodConfigurationUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\payment\Controller\AddPaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\payment\Controller\AddPaymentMethodConfiguration
 *
 * @group Payment
 */
class AddPaymentMethodConfigurationUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\AddPaymentMethodConfiguration
   */
  protected $controller;

  /**
   * The current user used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityFormBuilder;

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment method configuration plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

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
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->entityFormBuilder = $this->getMock('\Drupal\Core\Entity\EntityFormBuilderInterface');

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->paymentMethodConfigurationManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface');

    $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->controller = new AddPaymentMethodConfiguration($this->requestStack, $this->stringTranslation, $this->entityManager, $this->paymentMethodConfigurationManager, $this->entityFormBuilder, $this->currentUser);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser],
      ['entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder],
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
      ['plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager],
      ['request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = AddPaymentMethodConfiguration::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\AddPaymentMethodConfiguration', $form);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $plugin_id = $this->randomMachineName();

    $payment_method_configuration = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');

    $storage_controller = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage_controller->expects($this->once())
      ->method('create')
      ->will($this->returnValue($payment_method_configuration));

    $form = $this->getMock('\Drupal\Core\Entity\EntityFormInterface');

    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with('payment_method_configuration')
      ->will($this->returnValue($storage_controller));

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($payment_method_configuration, 'default')
      ->will($this->returnValue($form));

    $this->controller->execute($plugin_id);
  }

  /**
   * @covers ::access
   */
  public function testAccess() {
    $plugin_id = $this->randomMachineName();
    $request = new Request();
    $request->attributes->set('plugin_id', $plugin_id);

    $this->requestStack->expects($this->atLeastOnce())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $access_controller = $this->getMock('\Drupal\Core\Entity\EntityAccessControlHandlerInterface');
    $access_controller->expects($this->at(0))
      ->method('createAccess')
      ->with($plugin_id, $this->currentUser, [], TRUE)
      ->will($this->returnValue(AccessResult::allowed()));
    $access_controller->expects($this->at(1))
      ->method('createAccess')
      ->with($plugin_id, $this->currentUser, [], TRUE)
      ->will($this->returnValue(AccessResult::forbidden()));

    $this->entityManager->expects($this->exactly(2))
      ->method('getAccessControlHandler')
      ->with('payment_method_configuration')
      ->will($this->returnValue($access_controller));

    $this->assertTrue($this->controller->access($request)->isAllowed());
    $this->assertFalse($this->controller->access($request)->isAllowed());
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $plugin_id = $this->randomMachineName();

    $this->paymentMethodConfigurationManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($plugin_id)
      ->will($this->returnValue([
        'label' => $this->randomMachineName(),
      ]));

    $this->assertInternalType('string', $this->controller->title($plugin_id));
  }

}
