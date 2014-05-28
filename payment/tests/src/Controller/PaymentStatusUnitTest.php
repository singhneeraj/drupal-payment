<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Controller\PaymentStatusUnitTest.
 */

namespace Drupal\payment\Tests\Controller;

use Drupal\Core\Access\AccessInterface;
use Drupal\payment\Controller\PaymentStatus;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\payment\Controller\PaymentStatus
 */
class PaymentStatusUnitTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\PaymentStatus
   */
  protected $controller;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityFormBuilder;

  /**
   * The payment method plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The payment status storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusStorage;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\Controller\PaymentStatus unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->entityFormBuilder = $this->getMock('\Drupal\Core\Entity\EntityFormBuilderInterface');

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->paymentStatusStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->controller = new PaymentStatus($this->stringTranslation, $this->entityFormBuilder, $this->paymentStatusManager, $this->paymentStatusStorage);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment_status')
      ->will($this->returnValue($this->paymentStatusStorage));

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = PaymentStatus::create($container);
    $this->assertInstanceOf('\Drupal\payment\Controller\PaymentStatus', $form);
  }

  /**
   * @covers ::editTitle
   */
  public function testEditTitle() {
    $label = $this->randomName();
    $string = 'Edit %label';

    $payment_status = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_status->expects($this->once())
      ->method('label')
      ->will($this->returnValue($label));

    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->with($string, array(
        '%label' => $label,
      ))
      ->will($this->returnArgument(0));

    $this->assertSame($string, $this->controller->editTitle($payment_status));
  }

  /**
   * @covers ::add
   */
  public function testAdd() {
    $payment_status = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusStorage->expects($this->once())
      ->method('create')
      ->will($this->returnValue($payment_status));

    $form = $this->getMock('\Drupal\Core\Form\FormInterface');

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($payment_status)
      ->will($this->returnValue($form));

    $this->assertSame($form, $this->controller->add());
  }

}
