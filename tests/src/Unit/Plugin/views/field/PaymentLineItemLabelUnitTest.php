<?php

/**
 * @file Contains \Drupal\Tests\payment\Unit\Plugin\views\field\PaymentLineItemLabelUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\views\field;

use Drupal\payment\Plugin\views\field\PaymentLineItemLabel;
use Drupal\Tests\UnitTestCase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\views\field\PaymentLineItemLabel
 *
 * @group Payment
 */
class PaymentLineItemLabelUnitTest extends UnitTestCase {

  /**
   * The line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\views\field\PaymentLineItemLabel
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentLineItemManager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->sut = new PaymentLineItemLabel($configuration, $plugin_id, $plugin_definition, $this->paymentLineItemManager);
    $options = [
      'relationship' => 'none',
    ];
    $view_executable = $this->getMockBuilder('\Drupal\views\ViewExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $display = $this->getMockBuilder('\Drupal\views\Plugin\views\display\DisplayPluginBase')
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->sut->init($view_executable, $display, $options);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $sut = PaymentLineItemLabel::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\views\field\PaymentLineItemLabel', $sut);
  }

  /**
   * @covers ::render
   */
  public function testRender() {
    $plugin_id = $this->randomMachineName();
    $plugin_label = $this->randomMachineName();

    $plugin_definition = [
      'label' => $plugin_label,
    ];

    $this->paymentLineItemManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($plugin_id)
      ->willReturn($plugin_definition);

    $result_row = new ResultRow();
    $result_row->{$this->sut->field_alias} = $plugin_id;

    $this->assertSame($plugin_label, $this->sut->render($result_row));
  }

}
