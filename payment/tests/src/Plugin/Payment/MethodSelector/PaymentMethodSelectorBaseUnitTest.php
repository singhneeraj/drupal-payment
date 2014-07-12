<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentMethodSelectorBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodSelector;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase
 *
 * @group Payment
 */
class PaymentMethodSelectorBaseUnitTest extends UnitTestCase {

  /**
   * The current user used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The payment method manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment method selector plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelectorPlugin;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $configuration = array();
    $plugin_id = $this->randomName();
    $plugin_definition = array();
    $this->paymentMethodSelectorPlugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->currentUser, $this->paymentMethodManager))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::create
   */
  public function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase $class_name */
    $class_name = get_class($this->paymentMethodSelectorPlugin);

    $payment_method_selector = $class_name::create($container, array(), $this->randomName(), array());
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase', $payment_method_selector);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->paymentMethodSelectorPlugin->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('allowed_payment_method_plugin_ids', $configuration);
    $this->assertNull($configuration['allowed_payment_method_plugin_ids']);
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame(array(), $this->paymentMethodSelectorPlugin->calculateDependencies());
  }

  /**
   * @covers ::validateConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = array();
    $form_state = array();
    $this->paymentMethodSelectorPlugin->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = array();
    $form_state = array();
    $this->paymentMethodSelectorPlugin->submitConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array($this->randomName());
    $this->assertSame($this->paymentMethodSelectorPlugin, $this->paymentMethodSelectorPlugin->setConfiguration($configuration));
    $this->assertSame($configuration, $this->paymentMethodSelectorPlugin->getConfiguration());
  }

  /**
   * @covers ::setPayment
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($this->paymentMethodSelectorPlugin, $this->paymentMethodSelectorPlugin->setPayment($payment));
    $this->assertSame($payment, $this->paymentMethodSelectorPlugin->getPayment());
  }

  /**
   * @covers ::setPaymentMethod
   * @covers ::getPaymentMethod
   */
  public function testGetPaymentMethod() {
    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $this->assertSame($this->paymentMethodSelectorPlugin, $this->paymentMethodSelectorPlugin->setPaymentMethod($payment_method));
    $this->assertSame($payment_method, $this->paymentMethodSelectorPlugin->getPaymentMethod());
  }

  /**
   * @covers ::setRequired
   * @covers ::isRequired
   */
  public function testGetRequired() {
    $this->assertFalse($this->paymentMethodSelectorPlugin->isRequired());
    $this->assertSame($this->paymentMethodSelectorPlugin, $this->paymentMethodSelectorPlugin->setRequired());
    $this->assertTrue($this->paymentMethodSelectorPlugin->isRequired());
    $this->paymentMethodSelectorPlugin->setRequired(FALSE);
    $this->assertFalse($this->paymentMethodSelectorPlugin->isRequired());
  }

  /**
   * @covers ::setAllowedPaymentMethods
   * @covers ::resetAllowedPaymentMethods
   * @covers ::getAllowedPaymentMethods
   */
  public function testGetAllowedPaymentMethods() {
    $ids = array($this->randomName(), $this->randomName());
    $this->assertSame($this->paymentMethodSelectorPlugin, $this->paymentMethodSelectorPlugin->setAllowedPaymentMethods($ids));
    $this->assertSame($ids, $this->paymentMethodSelectorPlugin->getAllowedPaymentMethods());
    $this->assertSame($this->paymentMethodSelectorPlugin, $this->paymentMethodSelectorPlugin->resetAllowedPaymentMethods());
    $this->assertSame(TRUE, $this->paymentMethodSelectorPlugin->getAllowedPaymentMethods());
  }

  /**
   * @covers ::getAvailablePaymentMethods
   * @depends testGetAllowedPaymentMethods
   */
  public function testGetAvailablePaymentMethods() {
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $plugin_id_access = $this->randomName();
    $plugin_access = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $plugin_access->expects($this->atLeastOnce())
      ->method('setPayment')
      ->with($payment);
    $plugin_access->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->currentUser)
      ->will($this->returnValue(TRUE));
    $plugin_access->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id_access));

    $plugin_id_no_access = $this->randomName();
    $plugin_no_access = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $plugin_access->expects($this->atLeastOnce())
      ->method('setPayment')
      ->with($payment);
    $plugin_no_access->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->currentUser)
      ->will($this->returnValue(FALSE));

    $definitions = array(
      $plugin_id_access => array(),
      $plugin_id_no_access => array(),
    );
    $this->paymentMethodManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
    $return_value_map = array(
      array($plugin_id_access, array(), $plugin_access),
      array($plugin_id_no_access, array(), $plugin_no_access),
    );
    $this->paymentMethodManager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValueMap($return_value_map));

    $this->paymentMethodSelectorPlugin->setPayment($payment);

    $method = new \ReflectionMethod($this->paymentMethodSelectorPlugin, 'getAvailablePaymentMethods');
    $method->setAccessible(TRUE);

    // Test with all methods allowed.
    $allowed_payment_methods = $method->invoke($this->paymentMethodSelectorPlugin, $payment);
    $this->assertInternalType('array', $allowed_payment_methods);
    $this->assertCount(1, $allowed_payment_methods);
    $this->assertArrayHasKey($plugin_id_access, $allowed_payment_methods);

    // Test with only the unavailable method allowed.
    $this->paymentMethodSelectorPlugin->setAllowedPaymentMethods(array($plugin_id_no_access));
    $allowed_payment_methods = $method->invoke($this->paymentMethodSelectorPlugin);
    $this->assertInternalType('array', $allowed_payment_methods);
    $this->assertCount(0, $allowed_payment_methods);
  }
}
