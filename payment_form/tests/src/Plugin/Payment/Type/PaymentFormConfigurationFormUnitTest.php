<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Payment\Type\PaymentFormConfigurationFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Payment\Type;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm
 */
class PaymentFormConfigurationFormUnitTest extends UnitTestCase {

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The form under test.
   *
   * @var \Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $form;

  /**
   * The payment method manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment method selector manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelectorManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->configFactory = $this->getMockBuilder('Drupal\Core\Config\ConfigFactory')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->paymentMethodSelectorManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface');

    $this->form = $this->getMockBuilder('\Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm')
      ->setConstructorArgs(array($this->configFactory, $this->paymentMethodManager, $this->paymentMethodSelectorManager))
      ->setMethods(array('t'))
      ->getMock();
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertInternalType('string', $this->form->getFormId());
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildForm() {
    $config = $this->getMockBuilder('\Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->exactly(2))
      ->method('get');

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('payment_form.payment_type')
      ->will($this->returnValue($config));

    $form = array();
    $form_state = array();
    $form = $this->form->buildForm($form, $form_state);
    $this->assertInternalType('array', $form);
  }

}
