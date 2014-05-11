<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Method\PaymentMethodBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method;

use Drupal\Core\Access\AccessInterface;
use Drupal\payment\Event\PaymentEvents;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase
 */
class PaymentMethodBaseUnitTest extends PaymentMethodBaseUnitTestBase {

  /**
   * The payment method plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Method\PaymentMethodBase unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodBase')
      ->setConstructorArgs(array(array(), '', $this->pluginDefinition, $this->moduleHandler, $this->eventDispatcher, $this->token))
      ->setMethods(array('getSupportedCurrencies', 'doExecutePayment', 'checkMarkup', 't'))
      ->getMock();
    $this->plugin->expects($this->any())
      ->method('checkMarkup')
      ->will($this->returnArgument(0));
    $this->plugin->expects($this->any())
      ->method('t')
      ->will($this->returnArgument(0));
  }

  /**
   * Tests defaultConfiguration().
   */
  public function testDefaultConfiguration() {
    $this->assertInternalType('array', $this->plugin->defaultConfiguration());
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array(
      $this->randomName() => mt_rand(),
    );
    $this->assertNull($this->plugin->setConfiguration($configuration));
    $this->assertSame($configuration, $this->plugin->getConfiguration());
  }

  /**
   * @covers ::getMessageText
   */
  public function testGetMessageText() {
    $this->assertSame($this->pluginDefinition['message_text'], $this->plugin->getMessageText());
  }

  /**
   * @covers ::getMessageTextFormat
   */
  public function testGetMessageTextFormat() {
    $this->assertSame($this->pluginDefinition['message_text_format'], $this->plugin->getMessageTextFormat());
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
    $this->assertArrayHasKey('message', $elements);
    $this->assertInternalType('array', $elements['message']);
  }

  /**
   * @covers ::executePayment
   */
  public function testExecutePayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->moduleHandler->expects($this->once())
      ->method('invokeAll')
      ->with('payment_pre_execute');
    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_PRE_EXECUTE);
    $this->plugin->executePayment($payment);
  }

  /**
   * @covers ::executePaymentAccess
   */
  public function testExecutePaymentAccess() {
    $currency_code = 'EUR';
    $valid_amount = 12.34;
    $minimum_amount = 10;
    $maximum_amount = 20;

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->plugin->expects($this->any())
      ->method('getSupportedCurrencies')
      ->will($this->returnValue(array(
        $currency_code => array(
          'minimum' => $minimum_amount,
          'maximum' => $maximum_amount,
        ),
      )));

    // Test granted access.
    // @todo Check how to test denial of access reliably.
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
    $this->eventDispatcher->expects($this->exactly(2))
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_EXECUTE_ACCESS);
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $this->assertTrue($this->plugin->executePaymentAccess($payment, $account));
    $this->assertTrue($this->plugin->executePaymentAccess($payment, $account));
  }
}
