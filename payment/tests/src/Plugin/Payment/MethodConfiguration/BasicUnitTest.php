<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodConfiguration\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodConfiguration {

use Drupal\payment\Plugin\Payment\MethodConfiguration\Basic;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic
 *
 * @group Payment
 */
class BasicUnitTest extends UnitTestCase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

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
    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->paymentMethodConfiguration = new Basic(array(), '', array(), $this->stringTranslation, $this->moduleHandler, $this->paymentStatusManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_definition = array();
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
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $elements = $this->paymentMethodConfiguration->buildConfigurationForm($form, $form_state);
    $form['plugin_form']['#process'][] = array($this->paymentMethodConfiguration, 'processBuildConfigurationForm');
    $this->assertArrayHasKey('message', $elements);
    $this->assertArrayHasKey('plugin_form', $elements);
    $this->assertSame(array(array($this->paymentMethodConfiguration, 'processBuildConfigurationForm')), $elements['plugin_form']['#process']);
  }

  /**
   * @covers ::processBuildConfigurationForm
   */
  public function testProcessBuildConfigurationForm() {
    $definitions = array(
      array(
        'id' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
      ),
      array(
        'id' => $this->randomMachineName(),
        'label' => $this->randomMachineName(),
      ),
    );
    $this->paymentStatusManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $element = array(
      '#parents' => array('foo', 'bar'),
    );
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $method = new \ReflectionMethod($this->paymentMethodConfiguration ,'processBuildConfigurationForm');
    $method->setAccessible(TRUE);
    $elements = $method->invokeArgs($this->paymentMethodConfiguration, array(&$element, $form_state, &$form));
    $this->assertInternalType('array', $elements);
    foreach (array('brand_label', 'execute', 'capture', 'refund') as $key) {
      $this->assertArrayHasKey($key, $elements);
      $this->assertInternalType('array', $elements[$key]);
    }
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $brand_label = $this->randomMachineName();
    $message = $this->randomMachineName();
    $execute_status_id = $this->randomMachineName();
    $capture = TRUE;
    $capture_status_id = $this->randomMachineName();
    $refund = TRUE;
    $refund_status_id = $this->randomMachineName();

    $form = array(
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
      'plugin_form' => array(
        'brand_label' => array(
          '#parents' => array('foo', 'bar', 'status')
        ),
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
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
      ));

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($brand_label, $this->paymentMethodConfiguration->getBrandLabel());
    $this->assertSame($execute_status_id, $this->paymentMethodConfiguration->getExecuteStatusId());
    $this->assertSame($capture, $this->paymentMethodConfiguration->getCapture());
    $this->assertSame($capture_status_id, $this->paymentMethodConfiguration->getCaptureStatusId());
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

}

namespace {

  if (!function_exists('drupal_get_path')) {
    function drupal_get_path() {}
  }
  if (!function_exists('drupal_html_id')) {
    function drupal_html_id() {}
  }

}

