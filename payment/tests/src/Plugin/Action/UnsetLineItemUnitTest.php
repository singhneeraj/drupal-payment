<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Action\UnsetLineItemUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Action;

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
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $configuration = array();
    $plugin_definition = array();
    $plugin_id = $this->randomName();
    $this->action = new UnsetLineItem($configuration, $plugin_id, $plugin_definition, $this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_definition = array();
    $plugin_id = $this->randomName();
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
