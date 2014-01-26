<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentMethodSelectorBaseUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodSelector;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase
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
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $configuration = array();
    $plugin_id = $this->randomName();
    $plugin_definition = array();
    $this->paymentMethodSelectorPlugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorBase')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->currentUser, $this->paymentMethodManager))
      ->setMethods(array('getPaymentMethodFromFormElements', 'formElements'))
      ->getMock();
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
   * @covers ::setConfiguration
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $configuration = array($this->randomName());
    $this->assertSame(spl_object_hash($this->paymentMethodSelectorPlugin), spl_object_hash($this->paymentMethodSelectorPlugin->setConfiguration($configuration)));
    $this->assertSame($configuration, $this->paymentMethodSelectorPlugin->getConfiguration());
  }

  /**
   * @covers ::setAllowedPaymentMethods
   * @covers ::resetAllowedPaymentMethods
   * @covers ::getAllowedPaymentMethods
   */
  public function testGetAllowedPaymentMethods() {
    $ids = array($this->randomName(), $this->randomName());
    $this->assertSame(spl_object_hash($this->paymentMethodSelectorPlugin), spl_object_hash($this->paymentMethodSelectorPlugin->setAllowedPaymentMethods($ids)));
    $this->assertSame($ids, $this->paymentMethodSelectorPlugin->getAllowedPaymentMethods());
    $this->assertSame(spl_object_hash($this->paymentMethodSelectorPlugin), spl_object_hash($this->paymentMethodSelectorPlugin->resetAllowedPaymentMethods()));
    $this->assertSame(NULL, $this->paymentMethodSelectorPlugin->getAllowedPaymentMethods());
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
    $plugin_access->expects($this->any())
      ->method('executePaymentAccess')
      ->with($payment, $this->currentUser)
      ->will($this->returnValue(TRUE));
    $plugin_access->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id_access));

    $plugin_id_no_access = $this->randomName();
    $plugin_no_access = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $plugin_no_access->expects($this->any())
      ->method('executePaymentAccess')
      ->with($payment, $this->currentUser)
      ->will($this->returnValue(FALSE));
    $plugin_no_access->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id_no_access));

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

    $method = new \ReflectionMethod($this->paymentMethodSelectorPlugin, 'getAvailablePaymentMethods');
    $method->setAccessible(TRUE);

    // Test with all methods allowed.
    $allowed_payment_methods = $method->invoke($this->paymentMethodSelectorPlugin, $payment);
    $this->assertInternalType('array', $allowed_payment_methods);
    $this->assertCount(1, $allowed_payment_methods);
    $this->assertArrayHasKey($plugin_id_access, $allowed_payment_methods);

    // Test with only the unavailable method allowed.
    $this->paymentMethodSelectorPlugin->setAllowedPaymentMethods(array($plugin_id_no_access));
    $allowed_payment_methods = $method->invoke($this->paymentMethodSelectorPlugin, $payment);
    $this->assertInternalType('array', $allowed_payment_methods);
    $this->assertCount(0, $allowed_payment_methods);
  }
}
