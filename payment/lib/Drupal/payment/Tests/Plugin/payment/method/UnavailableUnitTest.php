<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\payment\method\UnavailableUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\payment\Plugin\payment\method\Unavailable;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\payment\method\Unavailable.
 */
class UnavailableUnitTest extends UnitTestCase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The token API.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The payment method plugin.
   *
   * @var \Drupal\payment\Plugin\payment\method\Basic
   */
  protected $paymentMethodPlugin;

  /**
   * The payment method entity.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $paymentMethodEntity;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\payment\status\manager
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\method\Unavailable unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    parent::setUp();

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->token = $this->getMockBuilder('\Drupal\Core\Utility\Token')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusManager = $this->getMockBuilder('\Drupal\payment\Plugin\payment\status\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodPlugin = new Unavailable(array(), '', array(), $this->moduleHandler, $this->token, $this->paymentStatusManager);
  }

  /**
   * Tests defaultConfiguration().
   */
  public function testDefaultConfiguration() {
    $configuration = $this->paymentMethodPlugin->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertEmpty($configuration);
  }

  /**
   * Tests paymentFormElements().
   */
  public function testPaymentFormElements() {
    $form = array();
    $form_state = array();
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $elements = $this->paymentMethodPlugin->paymentMethodFormElements($form, $form_state, $payment);
    $this->assertInternalType('array', $elements);
    $this->assertEmpty($elements);
  }

  /**
   * Tests paymentMethodFormElements().
   */
  public function testPaymentMethodFormElements() {
    $form = array();
    $form_state = array();
    $elements = $this->paymentMethodPlugin->paymentMethodFormElements($form, $form_state);
    $this->assertInternalType('array', $elements);
    $this->assertEmpty($elements);
  }

  /**
   * Tests executePaymentAccess().
   */
  public function testExecutePaymentAccess() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertFalse($this->paymentMethodPlugin->executePaymentAccess($payment, $this->randomName(), $account));
  }

  /**
   * Tests executePayment().
   *
   * @expectedException \RuntimeException
   */
  public function testExecutePayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodPlugin->executePayment($payment);
  }

  /**
   * Tests brands().
   */
  public function testBrands() {
    $brands = $this->paymentMethodPlugin->brands();
    $this->assertInternalType('array', $brands);
  }

  /**
   * Tests setPaymentMethod() and getPaymentMethod().
   */
  public function testGetPaymentMethod() {
    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $this->assertSame(spl_object_hash($this->paymentMethodPlugin), spl_object_hash($this->paymentMethodPlugin->setPaymentMethod($payment_method)));
    $this->assertSame(spl_object_hash($payment_method), spl_object_hash($this->paymentMethodPlugin->getPaymentMethod()));
  }
}
