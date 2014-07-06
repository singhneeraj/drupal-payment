<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Method\PaymentMethodBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method {

use Drupal\Core\Access\AccessInterface;
use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\Event\PaymentExecuteAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   *
   * @covers ::__construct
   */
  public function setUp() {
    parent::setUp();

    $this->pluginDefinition['label'] = $this->randomName();

    $this->plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodBase')
      ->setConstructorArgs(array(array(), '', $this->pluginDefinition, $this->moduleHandler, $this->eventDispatcher, $this->token))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('token', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->token),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase $class_name */
    $class_name = get_class($this->plugin);
    $form = $class_name::create($container, array(), '', $this->pluginDefinition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\Method\PaymentMethodBase', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertSame(array(), $this->plugin->defaultConfiguration());
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame(array(), $this->plugin->calculateDependencies());
  }

  /**
   * @covers ::doExecutePaymentAccess
   */
  public function testDoExecutePaymentAccess() {
    $method = new \ReflectionMethod($this->plugin, 'doExecutePaymentAccess');
    $method->setAccessible(TRUE);

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertTrue($method->invoke($this->plugin, $account));
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
    $this->assertArrayHasKey('message', $elements);
    $this->assertInternalType('array', $elements['message']);
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

    $this->plugin->setPayment($payment);

    $this->plugin->executePayment();
  }

  /**
   * @covers ::executePaymentAccess
   *
   * @dataProvider providerTestExecutePaymentAccess
   */
  public function testExecutePaymentAccess($expected, $active, $currency_supported, $events, $do) {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->pluginDefinition['active'] = $active;
    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase|\PHPUnit_Framework_MockObject_MockObject $payment_method */
    $payment_method = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodBase')
      ->setConstructorArgs(array(array(), '', $this->pluginDefinition, $this->moduleHandler, $this->eventDispatcher, $this->token))
      ->setMethods(array('executePaymentAccessCurrency', 'executePaymentAccessEvent', 'doExecutePaymentAccess'))
      ->getMockForAbstractClass();
    $payment_method->expects($this->any())
      ->method('executePaymentAccessCurrency')
      ->with($account)
      ->will($this->returnValue($currency_supported));
    $payment_method->expects($this->any())
      ->method('executePaymentAccessEvent')
      ->with($account)
      ->will($this->returnValue($events));
    $payment_method->expects($this->any())
      ->method('doExecutePaymentAccess')
      ->with($account)
      ->will($this->returnValue($do));
    $payment_method->setPayment($payment);

    $this->assertSame($expected, $payment_method->executePaymentAccess($account));
  }

  /**
   * Provides data to self::testExecutePaymentAccess().
   */
  public function providerTestExecutePaymentAccess() {
    return array(
      array(TRUE, TRUE, TRUE, TRUE, TRUE),
      array(FALSE, FALSE, TRUE, TRUE, TRUE),
      array(FALSE, TRUE, FALSE, TRUE, TRUE),
      array(FALSE, TRUE, TRUE, FALSE, TRUE),
      array(FALSE, TRUE, TRUE, TRUE, FALSE),
    );
  }

  /**
   * @covers ::capturePayment
   */
  public function testCapturePayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->plugin->setPayment($payment);
    $this->plugin->expects($this->once())
      ->method('doCapturePayment');

    $this->plugin->capturePayment();
  }

  /**
   * @covers ::capturePayment
   *
   * @expectedException \Exception
   */
  public function testCapturePaymentWithoutPayment() {
    $this->plugin->capturePayment();
  }

  /**
   * @covers ::capturePaymentAccess
   *
   * @dataProvider providerTestCapturePaymentAccess
   */
  public function testCapturePaymentAccess($expected, $update_access, $do) {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('access')
      ->with('capture')
      ->will($this->returnValue($update_access));

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase|\PHPUnit_Framework_MockObject_MockObject $payment_method */
    $this->plugin->setPayment($payment);
    $this->plugin->expects($this->any())
      ->method('doCapturePaymentAccess')
      ->with($account)
      ->will($this->returnValue($do));

    $this->assertSame($expected, $this->plugin->capturePaymentAccess($account));
  }

  /**
   * Provides data to self::testCapturePaymentAccess().
   */
  public function providerTestCapturePaymentAccess() {
    return array(
      array(TRUE, TRUE, TRUE),
      array(FALSE, FALSE, TRUE),
      array(FALSE, TRUE, FALSE),
      array(FALSE, FALSE, FALSE),
    );
  }

  /**
   * @covers ::capturePaymentAccess
   *
   * @expectedException \Exception
   */
  public function testCapturePaymentAccessWithoutPayment() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->plugin->capturePaymentAccess($account);
  }

  /**
   * @covers ::executePaymentAccessEvent
   *
   * @dataProvider providerTestExecutePaymentAccessEvent
   */
  public function testExecutePaymentAccessEvent($expected, $event_results, $hook_results) {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->plugin->setPayment($payment);

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(PaymentEvents::PAYMENT_EXECUTE_ACCESS, $this->isInstanceOf('\Drupal\payment\Event\PaymentExecuteAccess'))
      ->will($this->returnCallback(function($type, PaymentExecuteAccess $event) use ($event_results) {
        foreach ($event_results as $event_result) {
          $event->setAccessResult($event_result);
        }
      }));

    $this->moduleHandler->expects($this->once())
      ->method('invokeAll')
      ->with('payment_execute_access', array($payment, $this->plugin, $account))
      ->will($this->returnValue($hook_results));

    $method = new \ReflectionMethod($this->plugin, 'executePaymentAccessEvent');
    $method->setAccessible(TRUE);

    $this->assertSame($expected, $method->invoke($this->plugin, $account));
  }

  /**
   * Provides data to self::testExecutePaymentAccessEvent().
   */
  public function providerTestExecutePaymentAccessEvent() {
    return array(
      // No access results.
      array(TRUE, array(), array()),
      // Some access results, all positive.
      array(TRUE, array(AccessInterface::ALLOW), array()),
      array(TRUE, array(), array(AccessInterface::ALLOW)),
      // Both ALLOW and DENY, but no KILL, so access is granted.
      array(TRUE, array(AccessInterface::ALLOW), array(AccessInterface::DENY)),
      array(TRUE, array(AccessInterface::DENY), array(AccessInterface::ALLOW)),
      // Various combinations of access denied.
      array(FALSE, array(AccessInterface::DENY), array()),
      array(FALSE, array(), array(AccessInterface::DENY)),
      array(FALSE, array(AccessInterface::KILL), array()),
      array(FALSE, array(), array(AccessInterface::KILL)),
      array(FALSE, array(AccessInterface::KILL), array(AccessInterface::ALLOW)),
      array(FALSE, array(AccessInterface::ALLOW), array(AccessInterface::KILL)),
    );
  }

  /**
   * @covers ::executePaymentAccessCurrency
   *
   * @dataProvider providerTestExecutePaymentAccessCurrency
   */
  public function testExecutePaymentAccessCurrency($expected, $supported_currencies, $payment_currency_code, $payment_amount) {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->atLeastOnce())
      ->method('getAmount')
      ->will($this->returnValue($payment_amount));
    $payment->expects($this->atLeastOnce())
      ->method('getCurrencyCode')
      ->will($this->returnValue($payment_currency_code));

    $this->plugin->setPayment($payment);
    $this->plugin->expects($this->atLeastOnce())
      ->method('getSupportedCurrencies')
      ->will($this->returnValue($supported_currencies));

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $method = new \ReflectionMethod($this->plugin, 'executePaymentAccessCurrency');
    $method->setAccessible(TRUE);

    $this->assertSame($expected, $method->invoke($this->plugin, $account));
  }

  /**
   * Provides data to self::testExecutePaymentAccessCurrency().
   */
  public function providerTestExecutePaymentAccessCurrency() {
    return array(
      // All currencies are allowed.
      array(TRUE, TRUE, $this->randomName(), mt_rand()),
      // The payment currency is allowed. No amount limitations.
      array(TRUE, array(
        'ABC' => array(),
      ), 'ABC', mt_rand()),
      // The payment currency is allowed with amount limitations.
      array(TRUE, array(
        'ABC' => array(
          'minimum' => 1,
          'maximum' => 3,
        ),
      ), 'ABC', 2),
      // The payment currency is not allowed.
      array(FALSE, array(
        'ABC' => array(),
      ), 'XXX', mt_rand()),
      // The payment currency is not allowed because of amount limitations.
      array(FALSE, array(
        'ABC' => array(
          'minimum' => 2,
        ),
      ), 'ABC', 1),
      array(FALSE, array(
        'ABC' => array(
          'maximum' => 1,
        ),
      ), 'ABC', 2),
    );
  }

  /**
   * @covers ::getPluginLabel
   */
  public function testGetPluginLabel() {
    $this->assertSame($this->pluginDefinition['label'], $this->plugin->getPluginLabel());
  }

}

}

namespace {

if (!function_exists('check_markup')) {
  function check_markup(){}
}

}
