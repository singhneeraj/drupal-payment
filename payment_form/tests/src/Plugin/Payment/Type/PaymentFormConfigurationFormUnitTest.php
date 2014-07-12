<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Payment\Type\PaymentFormConfigurationFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Payment\Type {

use Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm
 *
 * @group Payment Reference Field
 */
class PaymentFormConfigurationFormUnitTest extends UnitTestCase {

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The configuration the config factory returns.
   *
   * @see self::__construct
   *
   * @var array
   */
  protected $configFactoryConfiguration = array();

  /**
   * The form under test.
   *
   * @var \Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm
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
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->configContext = $this->getMock('\Drupal\Core\Config\Context\ContextInterface');

    $this->configFactoryConfiguration = array(
      'payment_form.payment_type' => array(
        'limit_allowed_payment_methods' => TRUE,
        'allowed_payment_method_ids' => array($this->randomName()),
        'payment_method_selector_id' => $this->randomName(),
      ),
    );

    $this->configFactory = $this->getConfigFactoryStub($this->configFactoryConfiguration);

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->paymentMethodSelectorManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->form = new PaymentFormConfigurationForm($this->configFactory, $this->stringTranslation, $this->paymentMethodManager, $this->paymentMethodSelectorManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('config.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configFactory),
      array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
      array('plugin.manager.payment.method_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodSelectorManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = PaymentFormConfigurationForm::create($container);
    $this->assertInstanceOf('\Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm', $form);
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
    $form = array();
    $form_state = array();
    $form = $this->form->buildForm($form, $form_state);
    $this->assertInternalType('array', $form);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $form = array();
    $form_state = array(
      'values' => array(
        'payment_method_selector_id' => $this->configFactoryConfiguration['payment_form.payment_type']['payment_method_selector_id'],
        'allowed_payment_method_ids' => $this->configFactoryConfiguration['payment_form.payment_type']['allowed_payment_method_ids'],
        'limit_allowed_payment_method' => !empty($this->configFactoryConfiguration['payment_form.payment_type']['payment_method_selector_id']),
      ),
    );
    $this->form->submitForm($form, $form_state);
  }

}

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {
  }
}

}
