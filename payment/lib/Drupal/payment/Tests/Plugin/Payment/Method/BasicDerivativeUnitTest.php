<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Method\BasicDerivativeUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Method;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\Payment\Method\BasicDerivative .
 */
class BasicDerivativeUnitTest extends UnitTestCase {

  /**
   * The plugin deriver under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\BasicDerivative|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $deriver;

  /**
   * The payment method configuration manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\Manager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment status storage controller used for testing.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodStorage;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Method\BasicDerivative unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    $this->paymentMethodConfigurationManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentMethodStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageControllerInterface');

    $this->deriver = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\BasicDerivative')
      ->setMethods(array('getPaymentMethodConfigurationManager', 'getPaymentMethodStorage'))
      ->getMock();
    $this->deriver->expects($this->any())
      ->method('getPaymentMethodConfigurationManager')
      ->will($this->returnValue($this->paymentMethodConfigurationManager));
    $this->deriver->expects($this->any())
      ->method('getPaymentMethodStorage')
      ->will($this->returnValue($this->paymentMethodStorage));
  }

  /**
   * Tests getDerivativeDefinitions().
   */
  public function testGetDerivativeDefinitions() {
    $id_enabled_basic = $this->randomName();
    $id_disabled_basic = $this->randomName();
    $brand_label = $this->randomName();
    $message_text = $this->randomName();
    $message_text_format = $this->randomName();
    $status = $this->randomName();

    $payment_method_enabled_basic = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method_enabled_basic->expects($this->any())
      ->method('status')
      ->will($this->returnValue(TRUE));
    $payment_method_enabled_basic->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id_enabled_basic));
    $payment_method_enabled_basic->expects($this->any())
      ->method('getPluginConfiguration')
      ->will($this->returnValue(array(
        'brand_label' => $brand_label,
        'message_text' => $message_text,
        'message_text_format' => $message_text_format,
        'status' => $status,
      )));
    $payment_method_enabled_basic->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('payment_basic'));

    $payment_method_disabled_basic = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method_disabled_basic->expects($this->any())
      ->method('status')
      ->will($this->returnValue(FALSE));
    $payment_method_disabled_basic->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id_disabled_basic));
    $payment_method_disabled_basic->expects($this->any())
      ->method('getPluginConfiguration')
      ->will($this->returnValue(array(
        'brand_label' => $brand_label,
        'message_text' => $message_text,
        'message_text_format' => $message_text_format,
        'status' => $status,
      )));
    $payment_method_disabled_basic->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('payment_basic'));

    $payment_method_enabled_no_basic = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method_enabled_no_basic->expects($this->any())
      ->method('status')
      ->will($this->returnValue(TRUE));
    $payment_method_enabled_no_basic->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($this->randomName()));

    $this->paymentMethodStorage->expects($this->once())
      ->method('loadMultiple')
      ->will($this->returnValue(array($payment_method_enabled_basic, $payment_method_enabled_no_basic, $payment_method_disabled_basic)));

    $payment_method_plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\Basic')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method_plugin->expects($this->any())
      ->method('getBrandLabel')
      ->will($this->returnValue($brand_label));
    $payment_method_plugin->expects($this->any())
      ->method('getMessageText')
      ->will($this->returnValue($message_text));
    $payment_method_plugin->expects($this->any())
      ->method('getMessageTextFormat')
      ->will($this->returnValue($message_text_format));
    $payment_method_plugin->expects($this->any())
      ->method('getStatus')
      ->will($this->returnValue($status));

    $this->paymentMethodConfigurationManager->expects($this->any())
      ->method('createInstance')
      ->with('payment_basic')
      ->will($this->returnValue($payment_method_plugin));

    $class = $this->randomName();
    $derivatives = $this->deriver->getDerivativeDefinitions(array(
      'class' => $class,
    ));
    $this->assertInternalType('array', $derivatives);
    $this->assertCount(2, $derivatives);
    $map = array(
      $id_enabled_basic => TRUE,
      $id_disabled_basic => FALSE,
    );
    foreach ($map as $id => $active) {
      $this->assertArrayHasKey($id, $derivatives);
      $this->assertArrayHasKey('active', $derivatives[$id]);
      $this->assertSame($active, $derivatives[$id]['active']);
      $this->assertArrayHasKey('class', $derivatives[$id]);
      $this->assertSame($class, $derivatives[$id]['class']);
      $this->assertArrayHasKey('label', $derivatives[$id]);
      $this->assertSame($brand_label, $derivatives[$id]['label']);
      $this->assertArrayHasKey('message_text', $derivatives[$id]);
      $this->assertSame($message_text, $derivatives[$id]['message_text']);
      $this->assertArrayHasKey('message_text_format', $derivatives[$id]);
      $this->assertSame($message_text_format, $derivatives[$id]['message_text_format']);
      $this->assertArrayHasKey('status', $derivatives[$id]);
      $this->assertSame($status, $derivatives[$id]['status']);
    }
  }
}
