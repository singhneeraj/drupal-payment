<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\SelectPaymentMethodConfigurationUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\payment\Controller\SelectPaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\SelectPaymentMethodConfiguration
 *
 * @group Payment
 */
class SelectPaymentMethodConfigurationUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\SelectPaymentMethodConfiguration
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
   * The payment method configuration plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->paymentMethodConfigurationManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface');

    $this->controller = new SelectPaymentMethodConfiguration($this->entityManager, $this->paymentMethodConfigurationManager, $this->currentUser);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser],
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
      ['plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager],
    ];
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = SelectPaymentMethodConfiguration::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\SelectPaymentMethodConfiguration', $form);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $definitions = [
      'payment_unavailable' => [],
      'foo' => [
        'description' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
      ],
      'bar' => [
        'description' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
      ],
    ];
    $this->paymentMethodConfigurationManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $access_controller = $this->getMock('\Drupal\Core\Entity\EntityAccessControlHandlerInterface');
    $access_controller->expects($this->any())
      ->method('createAccess')
      ->will($this->returnValue(TRUE));

    $this->entityManager->expects($this->once())
      ->method('getAccessControlHandler')
      ->with('payment_method_configuration')
      ->will($this->returnValue($access_controller));

    $this->controller->execute();
  }

  /**
   * @covers ::access
   */
  public function testAccess() {
    $definitions = [
      'payment_unavailable' => [],
      'foo' => [
        'description' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
      ],
      'bar' => [
        'description' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
      ],
    ];
    $this->paymentMethodConfigurationManager->expects($this->exactly(2))
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $access_controller = $this->getMock('\Drupal\Core\Entity\EntityAccessControlHandlerInterface');
    $access_controller->expects($this->at(0))
      ->method('createAccess')
      ->with('foo', $this->currentUser, [], TRUE)
      ->will($this->returnValue(AccessResult::allowed()));
    $access_controller->expects($this->at(1))
      ->method('createAccess')
      ->with('foo', $this->currentUser, [], TRUE)
      ->will($this->returnValue(AccessResult::forbidden()));
    $access_controller->expects($this->at(2))
      ->method('createAccess')
      ->with('bar', $this->currentUser, [], TRUE)
      ->will($this->returnValue(AccessResult::forbidden()));

    $this->entityManager->expects($this->exactly(2))
      ->method('getAccessControlHandler')
      ->with('payment_method_configuration')
      ->will($this->returnValue($access_controller));

    $this->assertTrue($this->controller->access()->isAllowed());
    $this->assertFalse($this->controller->access()->isAllowed());
  }

}
