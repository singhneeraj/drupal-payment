<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodConfiguration\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodConfiguration;

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
    foreach (array('brand_label', 'message_text', 'message_text_format', 'status') as $key) {
      $this->assertArrayHasKey($key, $configuration);
      $this->assertInternalType('string', $configuration[$key]);
    }
  }

  /**
   * @covers ::getStatus
   * @covers ::setStatus
   */
  public function testGetStatus() {
    $status = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentMethodConfiguration), spl_object_hash($this->paymentMethodConfiguration->setStatus($status)));
    $this->assertSame($status, $this->paymentMethodConfiguration->getStatus());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = array();
    $form_state = array();
    $elements = $this->paymentMethodConfiguration->buildConfigurationForm($form, $form_state);
    $this->assertInternalType('array', $elements);
    foreach (array('brand_label', 'message', 'status') as $key) {
      $this->assertArrayHasKey($key, $elements);
      $this->assertInternalType('array', $elements[$key]);
    }
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $status = $this->randomName();
    $brand_label = $this->randomName();
    $message = $this->randomName();

    $form = array(
      'status' => array(
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
            'status' => $status,
            'brand_label' => $brand_label,
            'message' => $message,
          ),
        ),
      ),
    );

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($status, $this->paymentMethodConfiguration->getStatus());
    $this->assertSame($brand_label, $this->paymentMethodConfiguration->getBrandLabel());
  }

  /**
   * @covers ::getBrandLabel
   * @covers ::setBrandLabel
   */
  public function testGetBrandLabel() {
    $label = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentMethodConfiguration), spl_object_hash($this->paymentMethodConfiguration->setBrandLabel($label)));
    $this->assertSame($label, $this->paymentMethodConfiguration->getBrandLabel());
  }
}
