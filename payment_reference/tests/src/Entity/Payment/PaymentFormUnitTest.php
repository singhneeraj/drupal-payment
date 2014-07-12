<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Entity\Payment\PaymentFormUnitTest.
 */

namespace Drupal\payment_reference\Tests\Entity\Payment;

use Drupal\payment_reference\Entity\Payment\PaymentForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_reference\Entity\Payment\PaymentForm
 *
 * @group Payment Reference Field
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The form under test.
   *
   * @var \Drupal\payment_reference\Entity\Payment\PaymentForm
   */
  protected $form;

  /**
   * The form display.
   *
   * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formDisplay;

  /**
   * A payment entity used for testing.
   *
   * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment method selector used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelector;

  /**
   * The payment method selector manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelectorManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->formDisplay = $this->getMock('\Drupal\Core\Entity\Display\EntityFormDisplayInterface');

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodSelector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');

    $this->paymentMethodSelectorManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface');

    $this->configFactory = $this->getConfigFactoryStub(array(
        'payment_reference.payment_type' => array(
          'limit_allowed_payment_methods' => FALSE,
          'allowed_payment_method_ids' => array(),
          'payment_selector_id' => 'payment_select',
        ),
    ));

    $this->form = new PaymentForm($this->entityManager, $this->paymentMethodSelectorManager);
    $this->form->setConfigFactory($this->configFactory);
    $this->form->setEntity($this->payment);
  }

  /**
   * @covers ::form
   */
  public function testForm() {
    $this->paymentMethodSelector->expects($this->once())
      ->method('buildConfigurationForm')
      ->will($this->returnValue(array()));

    $this->paymentMethodSelectorManager->expects($this->once())
      ->method('createInstance')
      ->with('payment_select')
      ->will($this->returnValue($this->paymentMethodSelector));

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $this->payment->expects($this->any())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->payment->expects($this->any())
      ->method('entityInfo')
      ->will($this->returnValue($entity_type));

    $form = array(
      'langcode' => array(),
    );
    $form_state = array();
    $this->form->setFormDisplay($this->formDisplay, $form_state);
    $build = $this->form->form($form, $form_state);
    $this->assertInternalType('array', $build);
    $this->assertArrayHasKey('line_items', $build);
    $this->assertSame(spl_object_hash($this->payment), spl_object_hash($build['line_items']['#payment']));
    $this->assertArrayHasKey('payment_method', $build);
  }

  /**
   * @covers ::buildEntity
   */
  public function testBuildEntity() {
    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $this->payment->expects($this->atLeastOnce())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $form = array();
    $form_state = array(
      'values' => array(),
    );
    $this->form->setFormDisplay($this->formDisplay, $form_state);
    $this->assertInstanceOf('\Drupal\payment\Entity\PaymentInterface', $this->form->buildEntity($form, $form_state));
  }

}
