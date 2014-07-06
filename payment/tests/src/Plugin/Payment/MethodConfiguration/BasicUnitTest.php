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
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodConfiguration\Basic unit test',
      'group' => 'Payment',
    );
  }

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
    $plugin_id = $this->randomName();
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
    $status = $this->randomName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setExecuteStatusId($status));
    $this->assertSame($status, $this->paymentMethodConfiguration->getExecuteStatusId());
  }

  /**
   * @covers ::getCaptureStatusId
   * @covers ::setCaptureStatusId
   */
  public function testGetCaptureStatusId() {
    $status = $this->randomName();
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
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = array();
    $form_state = array();
    $elements = $this->paymentMethodConfiguration->buildConfigurationForm($form, $form_state);
    $this->assertInternalType('array', $elements);
    foreach (array('brand_label', 'message', 'execute_status_id', 'capture_status_id_wrapper') as $key) {
      $this->assertArrayHasKey($key, $elements);
      $this->assertInternalType('array', $elements[$key]);
    }
    $this->assertArrayHasKey('capture_status_id', $elements['capture_status_id_wrapper']);
    $this->assertInternalType('array', $elements['capture_status_id_wrapper']['capture_status_id']);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $brand_label = $this->randomName();
    $message = $this->randomName();
    $execute_status_id = $this->randomName();
    $capture = TRUE;
    $capture_status_id = $this->randomName();

    $form = array(
      'brand_label' => array(
        '#parents' => array('foo', 'bar', 'status')
      ),
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
    );
    $form_state = array(
      'values' => array(
        'foo' => array(
          'bar' => array(
            'brand_label' => $brand_label,
            'message' => $message,
            'execute_status_id' => $execute_status_id,
            'capture' => $capture,
            'capture_status_id_wrapper' => array(
              'capture_status_id' => $capture_status_id,
            ),
          ),
        ),
      ),
    );

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
    $label = $this->randomName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setBrandLabel($label));
    $this->assertSame($label, $this->paymentMethodConfiguration->getBrandLabel());
  }
}

}

namespace {

  if (!function_exists('drupal_html_id')) {
    function drupal_html_id() {}
  }

}

