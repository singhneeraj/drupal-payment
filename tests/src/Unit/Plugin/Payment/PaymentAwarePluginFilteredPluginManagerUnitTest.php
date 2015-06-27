<?php

/**
 * @file
 * Contains \Drupal\Tests\Payment\Unit\Plugin\Payment\PaymentAwarePluginFilteredPluginManagerUnitTest.
 */

namespace Drupal\Tests\Payment\Unit\Plugin\Payment;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\PaymentAwareInterface;
use Drupal\payment\Plugin\Payment\PaymentAwarePluginFilteredPluginManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\PaymentAwarePluginFilteredPluginManager
 *
 * @group Payment
 */
class PaymentAwarePluginFilteredPluginManagerUnitTest extends UnitTestCase {

  /**
   * The payment to filter methods by.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The plugin definition mapper.
   *
   * @var \Drupal\plugin\Plugin\PluginDefinitionMapperInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginDefinitionMapper;

  /**
   * The original plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\PaymentAwarePluginFilteredPluginManager
   */
  protected $sut;

  public function setUp() {
    $this->payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $this->pluginDefinitionMapper = $this->getMock('\Drupal\plugin\Plugin\PluginDefinitionMapperInterface');

    $this->pluginManager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');

    $this->sut = new PaymentAwarePluginFilteredPluginManager($this->pluginManager, $this->pluginDefinitionMapper, $this->payment);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new PaymentAwarePluginFilteredPluginManager($this->pluginManager, $this->pluginDefinitionMapper, $this->payment);
  }

  /**
   * @covers ::createInstance
   */
  public function testCreateInstance() {
    $plugin_id_a = $this->randomMachineName();
    $plugin_a = $this->getMock('\Drupal\Component\Plugin\PluginInspectionInterface');
    $plugin_id_b = $this->randomMachineName();
    $plugin_b = $this->getMock('\Drupal\Tests\Payment\Unit\Plugin\Payment\PaymentAwarePluginFilteredPluginManagerUnitTestPaymentAwarePlugin');
    $plugin_b->expects($this->atLeastOnce())
      ->method('setPayment')
      ->with($this->payment);

    $map = [
      [$plugin_id_a, [], $plugin_a],
      [$plugin_id_b, [], $plugin_b],
    ];
    $this->pluginManager->expects($this->atLeast(count($map)))
      ->method('createInstance')
      ->willReturnMap($map);

    $this->assertSame($plugin_a, $this->sut->createInstance($plugin_id_a));
    $this->assertSame($plugin_b, $this->sut->createInstance($plugin_id_b));
  }

}

/**
 * Provides a payment-aware dummy plugin.
 */
abstract class PaymentAwarePluginFilteredPluginManagerUnitTestPaymentAwarePlugin implements PaymentAwareInterface, PluginInspectionInterface {
}
