<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\MethodConfiguration;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase
 *
 * @group Payment
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
   * The payment method plugin's definition.
   *
   * @var mixed[]
   */
  protected $pluginDefinition;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

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

    $this->pluginDefinition = array(
      'description' => $this->randomMachineName(),
      'label' => $this->randomMachineName(),
    );
    $this->paymentMethodConfiguration = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase')
      ->setConstructorArgs(array(array(), '', $this->pluginDefinition, $this->stringTranslation, $this->moduleHandler))
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
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame(array(), $this->paymentMethodConfiguration->calculateDependencies());
  }

  /**
   * @covers ::getMessageText
   * @covers ::setMessageText
   */
  public function testGetMessageText() {
    $message_text = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setMessageText($message_text));
    $this->assertSame($message_text, $this->paymentMethodConfiguration->getMessageText());
  }

  /**
   * @covers ::getMessageTextFormat
   * @covers ::setMessageTextFormat
   */
  public function testGetMessageTextFormat() {
    $message_text_format = $this->randomMachineName();
    $this->assertSame($this->paymentMethodConfiguration, $this->paymentMethodConfiguration->setMessageTextFormat($message_text_format));
    $this->assertSame($message_text_format, $this->paymentMethodConfiguration->getMessageTextFormat());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithoutFilter() {
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(FALSE));

    $message_text = $this->randomMachineName();
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
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(TRUE));

    $message_text = $this->randomMachineName();
    $message_format = $this->randomMachineName();
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
      $this->randomMachineName() => $this->randomMachineName(),
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
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $this->paymentMethodConfiguration->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationFormWithoutFilter() {
    $message_text = $this->randomMachineName();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(FALSE));

    $form = array(
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'message' => $message_text,
          ),
        ),
      ));

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($message_text, $this->paymentMethodConfiguration->getMessageText());
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationFormWithFilter() {
    $message_text = $this->randomMachineName();
    $message_format = $this->randomMachineName();

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('filter')
      ->will($this->returnValue(TRUE));

    $form = array(
      'message' => array(
        '#parents' => array('foo', 'bar', 'message')
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'message' => array(
              'value' => $message_text,
              'format' => $message_format,
            ),
          ),
        ),
      ));

    $this->paymentMethodConfiguration->submitConfigurationForm($form, $form_state);

    $this->assertSame($message_text, $this->paymentMethodConfiguration->getMessageText());
    $this->assertSame($message_format, $this->paymentMethodConfiguration->getMessageTextFormat());
  }

  /**
   * @covers ::getPluginLabel
   */
  public function testGetPluginLabel() {
    $this->assertSame($this->pluginDefinition['label'], $this->paymentMethodConfiguration->getPluginLabel());
  }

  /**
   * @covers ::getPluginDescription
   */
  public function testGetPluginDescription() {
    $this->assertSame($this->pluginDefinition['description'], $this->paymentMethodConfiguration->getPluginDescription());
  }

}
