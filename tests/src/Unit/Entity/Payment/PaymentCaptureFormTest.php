<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\Payment\PaymentCaptureFormTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\payment\Entity\Payment\PaymentCaptureForm;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodCapturePaymentInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentCaptureForm
 *
 * @group Payment
 */
class PaymentCaptureFormTest extends UnitTestCase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentCaptureForm
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->payment = $this->getMock(PaymentInterface::class);

    $this->sut = new PaymentCaptureForm($this->entityManager, $this->stringTranslation);
    $this->sut->setEntity($this->payment);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = PaymentCaptureForm::create($container);
    $this->assertInstanceOf(PaymentCaptureForm::class, $sut);
  }

  /**
   * @covers ::getConfirmText
   */
  function testGetConfirmText() {
    $this->assertInternalType('string', $this->sut->getConfirmText());
  }

  /**
   * @covers ::getQuestion
   */
  function testGetQuestion() {
    $this->assertInternalType('string', $this->sut->getQuestion());
  }

  /**
   * @covers ::getCancelUrl
   */
  function testGetCancelUrl() {
    $url = new Url($this->randomMachineName());

    $this->payment->expects($this->atLeastOnce())
      ->method('urlInfo')
      ->with('canonical')
      ->willReturn($url);

    $this->assertSame($url, $this->sut->getCancelUrl());
  }

  /**
   * @covers ::submitForm
   */
  function testSubmitForm() {
    $payment_method = $this->getMock(PaymentMethodCapturePaymentInterface::class);
    $payment_method->expects($this->once())
      ->method('capturePayment');

    $url = new Url($this->randomMachineName());

    $this->payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->willReturn($payment_method);
    $this->payment->expects($this->atLeastOnce())
      ->method('urlInfo')
      ->with('canonical')
      ->willReturn($url);

    $form = [];

    $form_state = $this->getMock(FormStateInterface::class);
    $form_state->expects($this->once())
      ->method('setRedirectUrl')
      ->with($url);

    $this->sut->submitForm($form, $form_state);
  }

}
