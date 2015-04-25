<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Action\SetStatusUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Action;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Form\FormState;
use Drupal\payment\Plugin\Action\SetStatus;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Action\SetStatus
 *
 * @group Payment
 */
class SetStatusUnitTest extends UnitTestCase {

  /**
   * The action under test.
   *
   * @var \Drupal\payment\Plugin\Action\SetStatus
   */
  protected $action;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelectorManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->pluginSelectorManager = $this->getMock('\Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $this->action = new SetStatus($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->pluginSelectorManager, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('plugin.manager.payment.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $form = SetStatus::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Action\SetStatus', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->action->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('payment_status_plugin_id', $configuration);
  }

  /**
   * @covers ::buildConfigurationForm
   * @covers ::getPluginSelector
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = new FormState();

    $plugin_selector_form = [
      '#foo' => $this->randomMachineName(),
    ];

    $plugin_selector = $this->getMock('\Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface');
    $plugin_selector->expects($this->once())
      ->method('buildSelectorForm')
      ->with([], $form_state)
      ->willReturn($plugin_selector_form);

    $this->pluginSelectorManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($plugin_selector);

    $expected_form = [
      'payment_status_plugin_id' => $plugin_selector_form,
    ];

    $form = $this->action->buildConfigurationForm($form, $form_state);
    $this->assertSame($expected_form, $form);
  }

  /**
   * @covers ::validateConfigurationForm
   * @covers ::getPluginSelector
   *
   * @depends testBuildConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = [
      'payment_status_plugin_id' => [
        '#foo' => $this->randomMachineName(),
      ],
    ];
    $form_state = new FormState();

    $plugin_selector = $this->getMock('\Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface');
    $plugin_selector->expects($this->once())
      ->method('validateSelectorForm')
      ->with($form['payment_status_plugin_id'], $form_state);

    $this->pluginSelectorManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($plugin_selector);

    $this->action->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   * @covers ::getPluginSelector
   *
   * @depends testBuildConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = [
      'payment_status_plugin_id' => [
        '#foo' => $this->randomMachineName(),
      ],
    ];
    $form_state = new FormState();

    $plugin_id = $this->randomMachineName();

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $payment_status->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->willReturn($plugin_id);


    $plugin_selector = $this->getMock('\Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface');
    $plugin_selector->expects($this->once())
      ->method('getSelectedPlugin')
      ->willReturn($payment_status);
    $plugin_selector->expects($this->once())
      ->method('submitSelectorForm')
      ->with($form['payment_status_plugin_id'], $form_state);

    $this->pluginSelectorManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($plugin_selector);

    $this->action->submitConfigurationForm($form, $form_state);
    $configuration = $this->action->getConfiguration();
    $this->assertSame($plugin_id, $configuration['payment_status_plugin_id']);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $plugin_id = $this->randomMachineName();

    $status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id)
      ->will($this->returnValue($status));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('setPaymentStatus')
      ->with($status);

    $this->action->setConfiguration(array(
      'payment_status_plugin_id' => $plugin_id,
    ));

    // Test execution without an argument to make sure it fails silently.
    $this->action->execute();
    $this->action->execute($payment);
  }

  /**
   * @covers ::access
   */
  public function testAccessWithPaymentAsObject() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $access_result = new AccessResultAllowed();

    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');
    $payment->expects($this->atLeastOnce())
      ->method('access')
      ->with('update', $account, TRUE)
      ->willReturn($access_result);

    $this->assertSame($access_result, $this->action->access($payment, $account, TRUE));
  }

  /**
   * @covers ::access
   */
  public function testAccessWithPaymentAsBoolean() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');
    $payment->expects($this->atLeastOnce())
      ->method('access')
      ->with('update', $account)
      ->willReturn(TRUE);

    $this->assertTrue($this->action->access($payment, $account));
  }

  /**
   * @covers ::access
   */
  public function testAccessWithoutPaymentAsObject() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $access_result = $this->action->access(NULL, $account, TRUE);
    $this->assertFalse($access_result->isAllowed());
  }

  /**
   * @covers ::access
   */
  public function testAccessWithoutPaymentAsBoolean() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $access_result = $this->action->access(NULL, $account);
    $this->assertFalse($access_result);
  }

}
