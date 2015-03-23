<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Method\BasicDeriverUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\BasicDeriver;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\BasicDeriver
 *
 * @group Payment
 */
class BasicDeriverUnitTest extends UnitTestCase {

  /**
   * The plugin deriver under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\BasicDeriver
   */
  protected $deriver;

  /**
   * The payment method configuration manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->paymentMethodConfigurationManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface');

    $this->paymentMethodConfigurationStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->deriver = new BasicDeriver($this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment_method_configuration')
      ->will($this->returnValue($this->paymentMethodConfigurationStorage));

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = BasicDeriver::create($container, [], '', []);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\Method\BasicDeriver', $form);
  }

  /**
   * @covers ::getDerivativeDefinitions
   */
  public function testGetDerivativeDefinitions() {
    $id_enabled_basic = $this->randomMachineName();
    $id_disabled_basic = $this->randomMachineName();
    $brand_label = $this->randomMachineName();
    $message_text = $this->randomMachineName();
    $message_text_format = $this->randomMachineName();
    $execute_status_id = $this->randomMachineName();
    $capture = TRUE;
    $capture_status_id = $this->randomMachineName();
    $refund = TRUE;
    $refund_status_id = $this->randomMachineName();

    $payment_method_enabled_basic = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');
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
        'execute_status_id' => $execute_status_id,
        'capture' => $capture,
        'capture_status_id' => $capture_status_id,
        'refund' => $refund,
        'refund_status_id' => $refund_status_id,
      )));
    $payment_method_enabled_basic->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('payment_basic'));

    $payment_method_disabled_basic = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');
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
        'execute_status_id' => $execute_status_id,
        'capture' => $capture,
        'capture_status_id' => $capture_status_id,
        'refund' => $refund,
        'refund_status_id' => $refund_status_id,
      )));
    $payment_method_disabled_basic->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('payment_basic'));

    $payment_method_enabled_no_basic = $this->getMock('\Drupal\payment\Entity\PaymentMethodConfigurationInterface');
    $payment_method_enabled_no_basic->expects($this->any())
      ->method('status')
      ->will($this->returnValue(TRUE));
    $payment_method_enabled_no_basic->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($this->randomMachineName()));

    $this->paymentMethodConfigurationStorage->expects($this->once())
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
      ->method('getExecuteStatusId')
      ->will($this->returnValue($execute_status_id));
    $payment_method_plugin->expects($this->any())
      ->method('getCaptureStatusId')
      ->will($this->returnValue($capture_status_id));
    $payment_method_plugin->expects($this->any())
      ->method('getCapture')
      ->will($this->returnValue($capture));
    $payment_method_plugin->expects($this->any())
      ->method('getRefundStatusId')
      ->will($this->returnValue($refund_status_id));
    $payment_method_plugin->expects($this->any())
      ->method('getRefund')
      ->will($this->returnValue($refund));

    $this->paymentMethodConfigurationManager->expects($this->any())
      ->method('createInstance')
      ->with('payment_basic')
      ->will($this->returnValue($payment_method_plugin));

    $class = $this->randomMachineName();
    $derivatives = $this->deriver->getDerivativeDefinitions(array(
      'class' => $class,
      'id' => $this->randomMachineName(),
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
      $this->assertArrayHasKey('execute_status_id', $derivatives[$id]);
      $this->assertSame($execute_status_id, $derivatives[$id]['execute_status_id']);
      $this->assertArrayHasKey('capture', $derivatives[$id]);
      $this->assertSame($capture, $derivatives[$id]['capture']);
      $this->assertArrayHasKey('capture_status_id', $derivatives[$id]);
      $this->assertSame($capture_status_id, $derivatives[$id]['capture_status_id']);
    }
  }
}
