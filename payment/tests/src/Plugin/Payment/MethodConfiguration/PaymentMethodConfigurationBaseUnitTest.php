<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodConfiguration;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase
 */
class PaymentMethodConfigurationBaseUnitTest extends UnitTestCase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The payment method configuration plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfiguration;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase unit test',
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

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->paymentMethodConfiguration = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase')
      ->setConstructorArgs(array(array(), '', array(), $this->stringTranslation, $this->moduleHandler))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase $class_name */
    $class_name = get_class($this->paymentMethodConfiguration);
    $form = $class_name::create($container, array(), '', array());
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->paymentMethodConfiguration->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    foreach (array('message_text', 'message_text_format') as $key) {
      $this->assertArrayHasKey($key, $configuration);
      $this->assertInternalType('string', $configuration[$key]);
    }
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithoutFilter() {
    $form = array();
    $form_state = array();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(FALSE));

    $message_text = $this->randomName();
    $this->paymentMethodConfiguration->setMessageText($message_text);

    $build = $this->paymentMethodConfiguration->buildConfigurationForm($form, $form_state);

    $expected_build = array(
      'message' => array(
        '#tree' => TRUE,
        '#type' => 'textarea',
        '#title' => 'Payment form message',
        '#default_value' => $message_text,
      )
    );

    $this->assertEquals($expected_build, $build);
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithFilter() {
    $form = array();
    $form_state = array();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(TRUE));

    $message_text = $this->randomName();
    $message_format = $this->randomName();
    $this->paymentMethodConfiguration->setMessageText($message_text);
    $this->paymentMethodConfiguration->setMessageTextFormat($message_format);

    $build = $this->paymentMethodConfiguration->buildConfigurationForm($form, $form_state);

    $expected_build = array(
      'message' => array(
        '#tree' => TRUE,
        '#type' => 'text_format',
        '#title' => 'Payment form message',
        '#default_value' => $message_text,
        '#format' => $message_format,
      )
    );

    $this->assertEquals($expected_build, $build);
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      $this->randomName() => $this->randomName(),
    );
    $return = $this->paymentMethodConfiguration->setConfiguration($configuration);
    $this->assertSame(NULL, $return);
    $this->assertSame($configuration, $this->paymentMethodConfiguration->getConfiguration());
  }

  /**
   * @covers ::validateConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = array();
    $form_state = array();
    $this->paymentMethodConfiguration->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationFormWithoutFilter() {
    $message_text = $this->randomName();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(FALSE));

    $form = array(
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
    );
    $form_state = array(
      'values' => array(
        'foo' => array(
          'bar' => array(
            'message' => $message_text,
          ),
        ),
      ),
    );

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($message_text, $this->paymentMethodConfiguration->getMessageText());
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationFormWithFilter() {
    $message_text = $this->randomName();
    $message_format = $this->randomName();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(TRUE));

    $form = array(
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
    );
    $form_state = array(
      'values' => array(
        'foo' => array(
          'bar' => array(
            'message' => array(
              'value' => $message_text,
              'format' => $message_format,
            ),
          ),
        ),
      ),
    );

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($message_text, $this->paymentMethodConfiguration->getMessageText());
    $this->assertSame($message_format, $this->paymentMethodConfiguration->getMessageTextFormat());
  }

}
