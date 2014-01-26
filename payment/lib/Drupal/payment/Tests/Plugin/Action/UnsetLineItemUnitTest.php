<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Action\UnsetLineItemUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Action;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Action\UnsetLineItem
 */
class UnsetLineItemUnitTest extends UnitTestCase {

  /**
   * The action under test.
   *
   * @var \Drupal\payment\Plugin\Action\UnsetLineItem|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Action\UnsetLineItem unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $configuration = array();
    $plugin_definition = array();
    $plugin_id = $this->randomName();
    $this->action = $this->getMockBuilder('\Drupal\payment\Plugin\Action\UnsetLineItem')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition))
      ->setMethods(array('t'))
      ->getMock();
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
    $form = array();
    $form_state = array();
    $form = $this->action->buildConfigurationForm($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->assertArrayHasKey('line_item_name', $form);
  }

  /**
   * @covers ::submitConfigurationForm
   * @depends testBuildConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $name = $this->randomName();
    $form = array();
    $form_state = array(
      'values' => array(
        'line_item_name' => $name,
      ),
    );
    $this->action->submitConfigurationForm($form, $form_state);
    $configuration = $this->action->getConfiguration();
    $this->assertSame($name, $configuration['line_item_name']);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $name = $this->randomName();

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
}
