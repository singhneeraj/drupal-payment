<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\SelectPaymentMethodConfigurationTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Controller\SelectPaymentMethodConfiguration;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\SelectPaymentMethodConfiguration
 *
 * @group Payment
 */
class SelectPaymentMethodConfigurationTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment method configuration manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\SelectPaymentMethodConfiguration
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->currentUser = $this->getMock(AccountInterface::class);

    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->paymentMethodConfigurationManager = $this->getMock(PaymentMethodConfigurationManagerInterface::class);

    $this->sut = new SelectPaymentMethodConfiguration($this->entityManager, $this->paymentMethodConfigurationManager, $this->currentUser);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = [
      ['current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser],
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
      ['plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = SelectPaymentMethodConfiguration::create($container);
    $this->assertInstanceOf(SelectPaymentMethodConfiguration::class, $sut);
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
      ->willReturn($definitions);

    $access_control_handler = $this->getMock(EntityAccessControlHandlerInterface::class);
    $access_control_handler->expects($this->any())
      ->method('createAccess')
      ->willReturn(TRUE);

    $this->entityManager->expects($this->once())
      ->method('getAccessControlHandler')
      ->with('payment_method_configuration')
      ->willReturn($access_control_handler);

    $this->sut->execute();
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
      ->willReturn($definitions);

    $access_control_handler = $this->getMock(EntityAccessControlHandlerInterface::class);
    $access_control_handler->expects($this->at(0))
      ->method('createAccess')
      ->with('foo', $this->currentUser, [], TRUE)
      ->willReturn(AccessResult::allowed());
    $access_control_handler->expects($this->at(1))
      ->method('createAccess')
      ->with('foo', $this->currentUser, [], TRUE)
      ->willReturn(AccessResult::forbidden());
    $access_control_handler->expects($this->at(2))
      ->method('createAccess')
      ->with('bar', $this->currentUser, [], TRUE)
      ->willReturn(AccessResult::forbidden());

    $this->entityManager->expects($this->exactly(2))
      ->method('getAccessControlHandler')
      ->with('payment_method_configuration')
      ->willReturn($access_control_handler);

    $this->assertTrue($this->sut->access()->isAllowed());
    $this->assertFalse($this->sut->access()->isAllowed());
  }

}
