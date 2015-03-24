<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Action\UnsetLineItemUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Action;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\payment\Plugin\Action\UnsetLineItem;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Action\UnsetLineItem
 *
 * @group Payment
 */
class UnsetLineItemUnitTest extends UnitTestCase {

  /**
   * The action under test.
   *
   * @var \Drupal\payment\Plugin\Action\UnsetLineItem
   */
  protected $action;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $this->action = new UnsetLineItem($configuration, $plugin_id, $plugin_definition, $this->stringTranslation);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $form = UnsetLineItem::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Action\UnsetLineItem', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->action->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('line_item_name', $configuration);
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = $this->action->buildConfigurationForm($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->assertArrayHasKey('line_item_name', $form);
  }

  /**
   * @covers ::submitConfigurationForm
   * @depends testBuildConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $name = $this->randomMachineName();
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'line_item_name' => $name,
      ));
    $this->action->submitConfigurationForm($form, $form_state);
    $configuration = $this->action->getConfiguration();
    $this->assertSame($name, $configuration['line_item_name']);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $name = $this->randomMachineName();

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('unsetLineItem')
      ->with($name);

    $this->action->setConfiguration(array(
      'line_item_name' => $name,
    ));

    $this->action->execute($payment);
  }

  /**
   * @covers ::access
   */
  public function testAccessWithPaymentAsObject() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $access_result = new AccessResultAllowed();

    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');
    $payment->expects($this->atLeastOnce())
      ->method('access')
      ->with('update', $account, TRUE)
      ->willReturn($access_result);

    $this->assertSame($access_result, $this->action->access($payment, $account, TRUE));
  }

  /**
   * @covers ::access
   */
  public function testAccessWithPaymentAsBoolean() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');
    $payment->expects($this->atLeastOnce())
      ->method('access')
      ->with('update', $account)
      ->willReturn(TRUE);

    $this->assertTrue($this->action->access($payment, $account));
  }

  /**
   * @covers ::access
   */
  public function testAccessWithoutPaymentAsObject() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $access_result = $this->action->access(NULL, $account, TRUE);
    $this->assertFalse($access_result->isAllowed());
  }

  /**
   * @covers ::access
   */
  public function testAccessWithoutPaymentAsBoolean() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $access_result = $this->action->access(NULL, $account);
    $this->assertFalse($access_result);
  }

}
