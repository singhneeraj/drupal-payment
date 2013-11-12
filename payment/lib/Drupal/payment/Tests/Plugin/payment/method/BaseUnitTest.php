<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\payment\method\BaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\Core\Access\AccessInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\payment\method\Base.
 */
class BaseUnitTest extends UnitTestCase {

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
   * @var \Drupal\payment\Plugin\payment\method\Base
   */
  protected $paymentMethodPlugin;

  /**
   * The payment method entity.
   *
   * @var \Drupal\payment\Entity\PaymentMethodInterface
   */
  protected $paymentMethodEntity;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\method\Base unit test',
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

    $this->paymentMethodPlugin = $this->getMockBuilder('\Drupal\payment\Plugin\payment\method\Base')
      ->setConstructorArgs(array(array(), '', array(), $this->moduleHandler, $this->token))
      ->setMethods(array('brands', 'checkMarkup', 't'))
      ->getMock();
    $this->paymentMethodPlugin->expects($this->any())
      ->method('checkMarkup')
      ->will($this->returnArgument(0));
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
   * Tests getConfiguration().
   */
  public function testGetConfiguration() {
    $configuration = $this->paymentMethodPlugin->getConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('message_text', $configuration);
    $this->assertInternalType('string', $configuration['message_text']);
    $this->assertArrayHasKey('message_text_format', $configuration);
    $this->assertInternalType('string', $configuration['message_text_format']);
  }

  /**
   * Tests setPaymentMethod() and getPaymentMethod().
   */
  public function testGetPaymentMethod() {
    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $this->assertSame(spl_object_hash($this->paymentMethodPlugin), spl_object_hash($this->paymentMethodPlugin->setPaymentMethod($payment_method)));
    $this->assertSame(spl_object_hash($payment_method), spl_object_hash($this->paymentMethodPlugin->getPaymentMethod()));
  }

  /**
   * Tests setMessageText() and getMessageText().
   */
  public function testGetMessageText() {
    $text = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentMethodPlugin), spl_object_hash($this->paymentMethodPlugin->setMessageText($text)));
    $this->assertSame($text, $this->paymentMethodPlugin->getMessageText());
  }

  /**
   * Tests setMessageTextFormat() and getMessageTextFormat().
   */
  public function testGetMessageTextFormat() {
    $format = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentMethodPlugin), spl_object_hash($this->paymentMethodPlugin->setMessageTextFormat($format)));
    $this->assertSame($format, $this->paymentMethodPlugin->getMessageTextFormat());
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
    $this->paymentMethodPlugin->paymentFormElements($form, $form_state, $payment);
  }

  /**
   * Tests paymentMethodFormElements().
   */
  public function testPaymentMethodFormElements() {
    $form = array();
    $form_state = array();
    $elements = $this->paymentMethodPlugin->paymentMethodFormElements($form, $form_state);
    $this->assertInternalType('array', $elements);
    $this->assertArrayHasKey('message', $elements);
    $this->assertInternalType('array', $elements['message']);
  }

  /**
   * Tests executePayment().
   */
  public function testExecutePayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->moduleHandler->expects($this->once())
      ->method('invokeAll')
      ->with('payment_pre_execute');
    $this->paymentMethodPlugin->executePayment($payment);
  }

  /**
   * Tests executePaymentAccess().
   */
  public function testExecutePaymentAccess() {
    $payment_method_brand = $this->randomName();
    $currency_code = 'EUR';
    $valid_amount = 12.34;
    $minimum_amount = 10;
    $maximum_amount = 20;

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodPlugin->expects($this->any())
      ->method('brands')
      ->will($this->returnValue(array(
        $payment_method_brand => array(
          'currencies' => array(
            $currency_code => array(
              'minimum' => $minimum_amount,
              'maximum' => $maximum_amount,
            ),
          ),
        )
      )));

    // Test granted access.
    // @todo Check how to test denial of access reliably.
    $this->paymentMethodEntity->expects($this->exactly(2))
      ->method('status')
      ->will($this->returnValue(TRUE));
    $payment->expects($this->exactly(2))
      ->method('getCurrencyCode')
      ->will($this->returnValue($currency_code));
    $payment->expects($this->exactly(2))
      ->method('getAmount')
      ->will($this->returnValue($valid_amount));
    $this->moduleHandler->expects($this->at(0))
      ->method('invokeAll')
      ->will($this->returnValue(array(AccessInterface::ALLOW, AccessInterface::DENY)));
    $this->moduleHandler->expects($this->at(1))
      ->method('invokeAll')
      ->will($this->returnValue(array()));
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $this->assertTrue($this->paymentMethodPlugin->executePaymentAccess($payment, $payment_method_brand, $account));
    $this->assertTrue($this->paymentMethodPlugin->executePaymentAccess($payment, $payment_method_brand, $account));
  }
}
