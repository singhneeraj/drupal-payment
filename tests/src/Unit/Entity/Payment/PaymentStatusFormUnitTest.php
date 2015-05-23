<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\Payment\PaymentStatusFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment;

use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\payment\Entity\Payment\PaymentStatusForm;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodUpdatePaymentStatusInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentStatusForm
 *
 * @group Payment
 */
class PaymentStatusFormUnitTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStatusForm
   */
  protected $form;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The plugin selector.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelector;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelectorManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->paymentStatusManager = $this->getmock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->pluginSelector = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface');

    $this->pluginSelectorManager = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface');

    $this->urlGenerator = $this->getmock('\Drupal\Core\Routing\UrlGeneratorInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->form = new PaymentStatusForm($this->currentUser, $this->urlGenerator, $this->stringTranslation, $this->pluginSelectorManager, $this->paymentStatusManager);
    $this->form->setEntity($this->payment);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    \Drupal::setContainer($container);
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('plugin.manager.plugin_selector.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      array('url_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = PaymentStatusForm::create($container);
    $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentStatusForm', $form);
  }

  /**
   * @covers ::form
   * @covers ::getPluginSelector
   */
  public function testForm() {
    $form = [];
    $form_state = new FormState();

    $settable_payment_status_ids = array($this->randomMachineName());

    $language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

    $payment_method = $this->getMock('\Drupal\Tests\payment\Unit\Entity\Payment\PaymentStatusFormUnitTestDummyPaymentMethodUpdateStatusInterface');
    $payment_method->expects($this->once())
      ->method('getSettablePaymentStatuses')
      ->with($this->currentUser)
      ->will($this->returnValue($settable_payment_status_ids));

    $this->payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));
    $this->payment->expects($this->any())
      ->method('language')
      ->will($this->returnValue($language));

    $this->pluginSelectorManager->expects($this->once())
      ->method('createInstance')
      ->willReturn($this->pluginSelector);

    $plugin_selector_form = [
      'foo' => $this->randomMachineName(),
    ];

    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('buildSelectorForm')
      ->with([], $form_state)
      ->willReturn($plugin_selector_form);

    $build = $this->form->form($form, $form_state);
    $this->assertInternalType('array', $build);
    $this->assertArrayHasKey('payment_status', $build);
    $this->assertSame($plugin_selector_form, $build['payment_status']);

    // Build the form again to make sure the plugin selector is only created
    // once.
    $this->form->form($form, $form_state);
  }

  /**
   * @covers ::validateForm
   * @covers ::getPluginSelector
   */
  public function testValidateForm() {
    $form = [
      'payment_status' => [
        'foo' => $this->randomMachineName(),
      ],
    ];
    $form_state = new FormState();

    $this->pluginSelectorManager->expects($this->once())
      ->method('createInstance')
      ->willReturn($this->pluginSelector);

    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('validateSelectorForm')
      ->with($form['payment_status'], $form_state);

    $this->form->validateForm($form, $form_state);
  }

  /**
   * @covers ::submitForm
   * @covers ::getPluginSelector
   */
  public function testSubmitForm() {
    $form = [
      'payment_status' => [
        'foo' => $this->randomMachineName(),
      ],
    ];
    $form_state = new FormState();

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->pluginSelectorManager->expects($this->once())
      ->method('createInstance')
      ->willReturn($this->pluginSelector);

    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('getSelectedPlugin')
      ->willReturn($payment_status);
    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('submitSelectorForm')
      ->with($form['payment_status'], $form_state);

    $url = new Url($this->randomMachineName());

    $this->payment->expects($this->once())
      ->method('setPaymentStatus')
      ->with($payment_status);
    $this->payment->expects($this->once())
      ->method('save');
    $this->payment->expects($this->once())
      ->method('urlInfo')
      ->with('canonical')
      ->willReturn($url);

    $this->form->submitForm($form, $form_state);
    $this->assertSame($url, $form_state->getRedirect());
  }

  /**
   * @covers ::actions
   */
  public function testActions() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $method = new \ReflectionMethod($this->form, 'actions');
    $method->setAccessible(TRUE);
    $method->invokeArgs($this->form, array($form, $form_state));
  }

}

/**
 * Extends two interfaces, because we can only mock one.
 */
interface PaymentStatusFormUnitTestDummyPaymentMethodUpdateStatusInterface extends PaymentMethodUpdatePaymentStatusInterface, PaymentMethodInterface {
}
