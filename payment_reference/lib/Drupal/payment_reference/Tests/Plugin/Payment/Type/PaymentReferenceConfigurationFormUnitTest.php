<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Test\Plugin\Payment\Type\PaymentReferenceConfigurationFormUnitTest.
 */

namespace Drupal\payment_reference\Test\Plugin\Payment\Type;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm.
 */
class PaymentReferenceConfigurationFormUnitTest extends UnitTestCase {

  /**
   * The configuration context used for testing.
   *
   * @var \Drupal\Core\Config\Context\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configContext;

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The form under test.
   *
   * @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $form;

  /**
   * The payment method manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

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
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->configContext = $this->getMock('\Drupal\Core\Config\Context\ContextInterface');

    $this->configFactory = $this->getMockBuilder('Drupal\Core\Config\ConfigFactory')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodSelectorManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->form = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReferenceConfigurationForm')
      ->setConstructorArgs(array($this->configFactory, $this->configContext, $this->paymentMethodManager, $this->paymentMethodSelectorManager))
      ->setMethods(array('t'))
      ->getMock();
  }

  /**
   * Tests getFormId().
   */
  public function testGetFormId() {
    $this->assertInternalType('string', $this->form->getFormId());
  }

  /**
   * Tests buildForm().
   */
  public function testBuildForm() {
    $config = $this->getMockBuilder('\Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->exactly(2))
      ->method('get');

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('payment_reference.payment_type')
      ->will($this->returnValue($config));

    $form = array();
    $form_state = array();
    $form = $this->form->buildForm($form, $form_state);
    $this->assertInternalType('array', $form);
  }

  /**
   * Tests submitForm().
   */
  public function testSubmitForm() {
    $config = $this->getMockBuilder('\Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->exactly(2))
      ->method('set');
    $config->expects($this->once())
      ->method('save');

    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('payment_reference.payment_type')
      ->will($this->returnValue($config));

    $form = array();
    $form_state = array(
      'values' => array(
        'payment_method_selector_id' => 'payment_select',
        'allowed_payment_method_ids' => array(),
      ),
    );
    $this->form->submitForm($form, $form_state);
  }

}
