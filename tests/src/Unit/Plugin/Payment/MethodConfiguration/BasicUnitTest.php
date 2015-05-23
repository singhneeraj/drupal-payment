<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\MethodConfiguration\BasicUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\MethodConfiguration;

use Drupal\Core\Form\FormState;
use Drupal\payment\Plugin\Payment\MethodConfiguration\Basic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic
 *
 * @group Payment
 */
class BasicUnitTest extends PaymentMethodConfigurationBaseUnitTestBase {

  /**
   * The payment method configuration plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic
   */
  protected $paymentMethodConfiguration;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelectorManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->pluginSelectorManager = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface');

    $this->paymentMethodConfiguration = new Basic([], '', $this->pluginDefinition, $this->stringTranslation, $this->moduleHandler, $this->pluginSelectorManager, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('plugin.manager.plugin_selector.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $form = Basic::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\MethodConfiguration\Basic', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->paymentMethodConfiguration->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    foreach (array('brand_label', 'message_text', 'message_text_format', 'execute_status_id', 'capture_status_id') as $key) {
      $this->assertArrayHasKey($key, $configuration);
      $this->assertInternalType('string', $configuration[$key]);
    }
    $this->assertArrayHasKey('capture', $configuration);
    $this->assertInternalType('boolean', $configuration['capture']);
  }

  /**
   * @covers ::getExecuteStatusId
   * @covers ::setExecuteStatusId
   */
  public function testGetExecuteStatusId() {
    $status = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setExecuteStatusId($status));
    $this->assertSame($status, $this->paymentMethodConfiguration->getExecuteStatusId());
  }

  /**
   * @covers ::getCaptureStatusId
   * @covers ::setCaptureStatusId
   */
  public function testGetCaptureStatusId() {
    $status = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setCaptureStatusId($status));
    $this->assertSame($status, $this->paymentMethodConfiguration->getCaptureStatusId());
  }

  /**
   * @covers ::getCapture
   * @covers ::setCapture
   */
  public function testGetCapture() {
    $capture = TRUE;
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setCapture($capture));
    $this->assertSame($capture, $this->paymentMethodConfiguration->getCapture());
  }

  /**
   * @covers ::getRefundStatusId
   * @covers ::setRefundStatusId
   */
  public function testGetRefundStatusId() {
    $status = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setRefundStatusId($status));
    $this->assertSame($status, $this->paymentMethodConfiguration->getRefundStatusId());
  }

  /**
   * @covers ::getRefund
   * @covers ::setRefund
   */
  public function testGetRefund() {
    $refund = TRUE;
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setRefund($refund));
    $this->assertSame($refund, $this->paymentMethodConfiguration->getRefund());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = new FormState();
    $elements = $this->paymentMethodConfiguration->buildConfigurationForm($form, $form_state);
    $form['plugin_form']['#process'][] = array($this->paymentMethodConfiguration, 'processBuildConfigurationForm');
    $this->assertArrayHasKey('message', $elements);
    $this->assertArrayHasKey('plugin_form', $elements);
    $this->assertSame(array(array($this->paymentMethodConfiguration, 'processBuildConfigurationForm')), $elements['plugin_form']['#process']);
  }

  /**
   * @covers ::processBuildConfigurationForm
   * @covers ::getExecutePaymentStatusSelector
   * @covers ::getCapturePaymentStatusSelector
   * @covers ::getRefundPaymentStatusSelector
   * @covers ::getPaymentStatusSelector
   */
  public function testProcessBuildConfigurationForm() {
    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($payment_status);

    $payment_status_selector = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface');

    $this->pluginSelectorManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($payment_status_selector);

    $element = array(
      '#parents' => array('foo', 'bar'),
    );
    $form = [];
    $form_state = new FormState();

    $elements = $this->paymentMethodConfiguration->processBuildConfigurationForm($element, $form_state, $form);
    $this->assertInternalType('array', $elements);
    foreach (array('brand_label', 'execute', 'capture', 'refund') as $key) {
      $this->assertArrayHasKey($key, $elements);
      $this->assertInternalType('array', $elements[$key]);
    }
  }

  /**
   * @covers ::submitConfigurationForm
   * @covers ::getExecutePaymentStatusSelector
   * @covers ::getCapturePaymentStatusSelector
   * @covers ::getRefundPaymentStatusSelector
   * @covers ::getPaymentStatusSelector
   */
  public function testSubmitConfigurationForm() {
    $brand_label = $this->randomMachineName();
    $message = $this->randomMachineName();
    $execute_status_id = $this->randomMachineName();
    $capture = TRUE;
    $capture_status_id = $this->randomMachineName();
    $refund = TRUE;
    $refund_status_id = $this->randomMachineName();

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($payment_status);

    $payment_status_selector = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface');
    $payment_status_selector->expects($this->atLeastOnce())
      ->method('getSelectedPlugin')
      ->willReturn($payment_status);

    $this->pluginSelectorManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->willReturn($payment_status_selector);

    $form = array(
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
      'plugin_form' => array(
        'brand_label' => array(
          '#parents' => array('foo', 'bar', 'status')
        ),
        'execute' => [
          'execute_status' => [
            '#foo' => $this->randomMachineName(),
          ],
        ],
        'capture' => [
          'plugin_form' => [
            'capture_status' => [
              '#foo' => $this->randomMachineName(),
            ],
          ],
        ],
        'refund' => [
          'plugin_form' => [
            'refund_status' => [
              '#foo' => $this->randomMachineName(),
            ],
          ],
        ],
      ),
    );
    $form_state = new FormState();
    $form_state->setValues([
      'foo' => array(
        'bar' => array(
          'brand_label' => $brand_label,
          'message' => $message,
          'execute' => array(
            'execute_status_id' => $execute_status_id,
          ),
          'capture' => array(
            'capture' => $capture,
            'capture_status_id' => $capture_status_id,
          ),
          'refund' => array(
            'refund' => $refund,
            'refund_status_id' => $refund_status_id,
          ),
        ),
      ),
    ]);

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($brand_label, $this->paymentMethodConfiguration->getBrandLabel());
    $this->assertSame($capture, $this->paymentMethodConfiguration->getCapture());
    $this->assertSame($refund, $this->paymentMethodConfiguration->getRefund());
  }

  /**
   * @covers ::getBrandLabel
   * @covers ::setBrandLabel
   */
  public function testGetBrandLabel() {
    $label = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setBrandLabel($label));
    $this->assertSame($label, $this->paymentMethodConfiguration->getBrandLabel());
  }
}
