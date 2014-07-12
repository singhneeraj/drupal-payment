<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Method\UnavailableUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\Unavailable;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\Unavailable
 *
 * @group Payment
 */
class UnavailableUnitTest extends UnitTestCase {

  /**
   * The payment method plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Unavailable
   */
  protected $plugin;

  /**
   * The plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->token = $this->getMockBuilder('\Drupal\Core\Utility\Token')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->pluginDefinition = array(
      'label' => $this->randomName(),
    );

    $this->plugin = new Unavailable(array(), '', $this->pluginDefinition, $this->moduleHandler, $this->token, $this->paymentStatusManager);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertSame(array(), $this->plugin->defaultConfiguration());
  }

  /**
   * @covers ::getPluginLabel
   */
  public function testGetPluginLabel() {
    $this->assertSame($this->pluginDefinition['label'], $this->plugin->getPluginLabel());
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame(array(), $this->plugin->calculateDependencies());
  }

  /**
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $this->assertSame(array(), $this->plugin->getConfiguration());
  }

  /**
   * @covers ::setConfiguration
   */
  public function testSetConfiguration() {
    $this->assertSame($this->plugin, $this->plugin->setConfiguration(array()));
  }

  /**
   * @covers ::getSupportedCurrencies
   */
  public function testGetSupportedCurrencies() {
    $method = new \ReflectionMethod($this->plugin, 'getSupportedCurrencies');
    $method->setAccessible(TRUE);

    $this->assertSame(array(), $method->invoke($this->plugin));
  }

  /**
   * @covers ::setPayment
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertSame($this->plugin, $this->plugin->setPayment($payment));
    $this->assertSame($payment, $this->plugin->getPayment());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = array();
    $form_state = array();
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $elements = $this->plugin->buildConfigurationForm($form, $form_state, $payment);
    $this->assertInternalType('array', $elements);
    $this->assertEmpty($elements);
  }

  /**
   * @covers ::validateConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = array();
    $form_state = array();
    $this->plugin->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = array();
    $form_state = array();
    $this->plugin->submitConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::executePaymentAccess
   */
  public function testExecutePaymentAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertFalse($this->plugin->executePaymentAccess($account));
  }

  /**
   * @covers ::executePayment
   * @expectedException \RuntimeException
   */
  public function testExecutePayment() {
    $this->plugin->executePayment();
  }

  /**
   * @covers ::capturePaymentAccess
   */
  public function testCapturePaymentAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertFalse($this->plugin->capturePaymentAccess($account));
  }

  /**
   * @covers ::capturePayment
   * @expectedException \RuntimeException
   */
  public function testCapturePayment() {
    $this->plugin->capturePayment();
  }

}
