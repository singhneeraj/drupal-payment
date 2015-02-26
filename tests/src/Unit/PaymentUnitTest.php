<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PaymentUnitTest.
 */

namespace Drupal\Tests\payment\Unit;

use Drupal\payment\Payment;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @coversDefaultClass \Drupal\payment\Payment
 *
 * @group Payment
 */
class PaymentUnitTest extends UnitTestCase {

  /**
   * The host site's container.
   */
  protected $originalContainer;

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    \Drupal::unsetContainer();
  }

  /**
   * @covers ::lineItemManager
   */
  public function testLineItemManager() {
    $container = new Container();
    $line_item_manager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');
    $container->set('plugin.manager.payment.line_item', $line_item_manager);
    \Drupal::setContainer($container);
    $this->assertSame($line_item_manager, Payment::lineItemManager());
  }

  /**
   * @covers ::methodManager
   */
  public function testMethodManager() {
    $container = new Container();
    $method_manager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');
    $container->set('plugin.manager.payment.method', $method_manager);
    \Drupal::setContainer($container);
    $this->assertSame($method_manager, Payment::methodManager());
  }

  /**
   * @covers ::methodConfigurationManager
   */
  public function testMethodConfigurationManager() {
    $container = new Container();
    $method_configuration_manager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface');
    $container->set('plugin.manager.payment.method_configuration', $method_configuration_manager);
    \Drupal::setContainer($container);
    $this->assertSame($method_configuration_manager, Payment::methodConfigurationManager());
  }

  /**
   * @covers ::methodSelectorManager
   */
  public function testMethodSelectorManager() {
    $container = new Container();
    $method_selector_manager= $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface');
    $container->set('plugin.manager.payment.method_selector', $method_selector_manager);
    \Drupal::setContainer($container);
    $this->assertSame($method_selector_manager, Payment::methodSelectorManager());
  }

  /**
   * @covers ::statusManager
   */
  public function testStatusManager() {
    $container = new Container();
    $status_manager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');
    $container->set('plugin.manager.payment.status', $status_manager);
    \Drupal::setContainer($container);
    $this->assertSame($status_manager, Payment::statusManager());
  }

  /**
   * @covers ::typeManager
   */
  public function testTypeManager() {
    $container = new Container();
    $type_manager = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface');
    $container->set('plugin.manager.payment.type', $type_manager);
    \Drupal::setContainer($container);
    $this->assertSame($type_manager, Payment::typeManager());
  }

}
