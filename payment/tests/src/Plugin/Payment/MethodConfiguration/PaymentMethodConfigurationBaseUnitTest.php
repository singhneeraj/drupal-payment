<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodConfiguration;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase
 */
class PaymentMethodConfigurationBaseUnitTest extends UnitTestCase {

  /**
   * The payment method configuration plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $plugin;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

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
   */
  public function setUp() {
    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase')
      ->setConstructorArgs(array(array(), '', array(), $this->paymentStatusManager))
      ->setMethods(array('t'))
      ->getMock();
    $this->plugin->expects($this->any())
      ->method('t')
      ->will($this->returnArgument(0));
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->plugin->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    foreach (array('message_text', 'message_text_format') as $key) {
      $this->assertArrayHasKey($key, $configuration);
      $this->assertInternalType('string', $configuration[$key]);
    }
  }

  /**
   * @covers ::formElements
   */
  public function testFormElements() {
    $form = array();
    $form_state = array();
    $elements = $this->plugin->formElements($form, $form_state);
    $this->assertInternalType('array', $elements);
    $this->assertArrayHasKey('message', $elements);
    $this->assertInternalType('array', $elements['message']);
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      $this->randomName() => $this->randomName(),
    );
    $return = $this->plugin->setConfiguration($configuration);
    $this->assertSame(NULL, $return);
    $this->assertSame($configuration, $this->plugin->getConfiguration());
  }
}
