<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Method\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method;

use Drupal\Core\Access\AccessInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\Payment\Method\Basic.
 */
class BasicUnitTest extends UnitTestCase {

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
   * @var \Drupal\payment\Plugin\Payment\Method\Basic
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
   * @var \Drupal\payment\Plugin\Payment\Status\manager
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Method\Basic unit test',
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

    $this->paymentMethodEntity = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');

    $this->paymentStatusManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Status\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodPlugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\Basic')
      ->setConstructorArgs(array(array(), '', array(), $this->moduleHandler, $this->token, $this->paymentStatusManager))
      ->setMethods(array('t'))
      ->getMock();
    $this->paymentMethodPlugin->expects($this->any())
      ->method('t')
      ->will($this->returnArgument(0));
    $this->paymentMethodPlugin->setPaymentMethod($this->paymentMethodEntity);
  }

  /**
   * Tests defaultConfiguration().
   */
  public function testDefaultConfiguration() {
    $this->assertInternalType('array', $this->paymentMethodPlugin->defaultConfiguration());
  }

  /**
   * Tests getStatus() setStatus().
   */
  public function testGetStatus() {
    $status = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentMethodPlugin), spl_object_hash($this->paymentMethodPlugin->setStatus($status)));
    $this->assertSame($status, $this->paymentMethodPlugin->getStatus());
  }

  /**
   * Tests paymentMethodFormElements().
   */
  public function testPaymentMethodFormElements() {
    $form = array();
    $form_state = array();
    $elements = $this->paymentMethodPlugin->paymentMethodFormElements($form, $form_state);
    $this->assertInternalType('array', $elements);
    foreach (array('brand', 'message', 'status') as $key) {
      $this->assertArrayHasKey($key, $elements);
      $this->assertInternalType('array', $elements[$key]);
    }
  }

  /**
   * Tests executePaymentAccess().
   */
  public function testExecutePaymentAccess() {
    $currency_code = 'EUR';
    $valid_amount = 12.34;

    $payment_method_plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\Basic')
      ->setConstructorArgs(array(array(), '', array(), $this->moduleHandler, $this->token, $this->paymentStatusManager))
      ->setMethods(array('brands'))
      ->getMock();
    $payment_method_plugin->expects($this->once())
      ->method('brands')
      ->will($this->returnValue(array(
        'default' => array()
      )));
    $payment_method_plugin->setPaymentMethod($this->paymentMethodEntity);

    $this->paymentMethodEntity->expects($this->once())
      ->method('status')
      ->will($this->returnValue(TRUE));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('getCurrencyCode')
      ->will($this->returnValue($currency_code));
    $payment->expects($this->once())
      ->method('getAmount')
      ->will($this->returnValue($valid_amount));

    $this->moduleHandler->expects($this->once())
      ->method('invokeAll')
      ->will($this->returnValue(array(AccessInterface::ALLOW, AccessInterface::DENY)));

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertTrue($payment_method_plugin->executePaymentAccess($payment, 'default', $account));
    $this->assertFalse($payment_method_plugin->executePaymentAccess($payment, $this->randomName(), $account));
  }

  /**
   * Tests executePayment().
   */
  public function testExecutePayment() {
    $payment_status_plugin_id = $this->randomName();
    $payment_method_plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\Basic')
      ->setConstructorArgs(array(array(), '', array(), $this->moduleHandler, $this->token, $this->paymentStatusManager))
      ->setMethods(array('executePaymentAccess', 'getStatus'))
      ->getMock();
    $payment_method_plugin->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue($payment_status_plugin_id));

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($payment_status_plugin_id)
      ->will($this->returnValue($payment_status));

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $payment_type->expects($this->once())
      ->method('resumeContext');

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('save');
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));
    $payment->expects($this->once())
      ->method('setStatus')
      ->with($payment_status);

    $payment_method_plugin->executePayment($payment);
  }

  /**
   * Tests brands().
   */
  public function testBrands() {
    $brands = $this->paymentMethodPlugin->brands();
    $this->assertInternalType('array', $brands);
  }

  /**
   * Tests getBrandLabel().
   */
  public function testGetBrandLabel() {
    $label = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentMethodPlugin), spl_object_hash($this->paymentMethodPlugin->setBrandLabel($label)));
    $this->assertSame($label, $this->paymentMethodPlugin->getBrandLabel());
  }
}
