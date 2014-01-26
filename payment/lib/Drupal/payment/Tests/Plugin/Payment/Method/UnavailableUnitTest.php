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
 */
class UnavailableUnitTest extends UnitTestCase {

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The token API used for testing.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $token;

  /**
   * The payment method plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Unavailable
   */
  protected $plugin;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Method\Unavailable unit test',
      'group' => 'Payment',
    );
  }

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

    $this->plugin = new Unavailable(array(), '', array(), $this->moduleHandler, $this->token, $this->paymentStatusManager);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertSame(array(), $this->plugin->defaultConfiguration());
  }

  /**
   * @covers ::formElements
   */
  public function testFormElements() {
    $form = array();
    $form_state = array();
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $elements = $this->plugin->formElements($form, $form_state, $payment);
    $this->assertInternalType('array', $elements);
    $this->assertEmpty($elements);
  }

  /**
   * @covers ::executePaymentAccess
   */
  public function testExecutePaymentAccess() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertFalse($this->plugin->executePaymentAccess($payment, $account));
  }

  /**
   * @covers ::executePayment
   * @expectedException \RuntimeException
   */
  public function testExecutePayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->plugin->executePayment($payment);
  }
}
