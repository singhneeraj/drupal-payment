<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Test\Entity\PaymentFormControllerUnitTest.
 */

namespace Drupal\payment_reference\Test\Entity;

use Drupal\payment_reference\Entity\PaymentFormController;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_reference\Entity\PaymentFormController.
 */
class PaymentFormControllerUnitTest extends UnitTestCase {

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The form under test
   *
   * @var \Drupal\payment_reference\Entity\PaymentFormController
   */
  protected $form;

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
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\Manager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelectorManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Entity\PaymentFormController unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodSelector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');

    $this->paymentMethodSelectorManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->configFactory = $this->getConfigFactoryStub(array(
        'payment_reference.payment_type' => array(
          'allowed_payment_method_ids' => NULL,
          'payment_selector_id' => 'payment_select',
        ),
    ));

    $this->form = new PaymentFormController($this->paymentMethodSelectorManager);
    $this->form->setConfigFactory($this->configFactory);
    $this->form->setEntity($this->payment);
  }

  /**
   * Tests form().
   */
  public function testForm() {
    $this->paymentMethodSelector->expects($this->once())
      ->method('formElements')
      ->will($this->returnValue(array()));

    $this->paymentMethodSelectorManager->expects($this->once())
      ->method('createInstance')
      ->with('payment_select')
      ->will($this->returnValue($this->paymentMethodSelector));

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $this->payment->expects($this->any())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $form = array(
      'langcode' => array(),
    );
    $form_state = array();
    $build = $this->form->form($form, $form_state);
    $this->assertInternalType('array', $build);
    $this->assertArrayHasKey('line_items', $build);
    $this->assertSame(spl_object_hash($this->payment), spl_object_hash($build['line_items']['#payment']));
    $this->assertArrayHasKey('payment_method', $build);
  }

  /**
   * Tests buildEntity().
   */
  public function testBuildEntity() {
    $form = array();
    $form_state = array();
    $this->assertInstanceOf('\Drupal\payment\Entity\PaymentInterface', $this->form->buildEntity($form, $form_state));
  }

}
