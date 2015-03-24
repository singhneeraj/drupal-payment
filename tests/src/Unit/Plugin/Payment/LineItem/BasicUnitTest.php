<?php

/**
 * @file Contains \Drupal\Tests\payment\Unit\Plugin\Payment\LineItem\BasicUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\LineItem;

use Drupal\payment\Plugin\Payment\LineItem\Basic;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\LineItem\Basic
 *
 * @group Payment
 */
class BasicUnitTest extends UnitTestCase {

  /**
   * The database connection used for testing.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $database;

  /**
   * The line item under test.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\Basic
   */
  protected $lineItem;

  /**
   * The math service used for testing.
   *
   * @var \Drupal\currency\Math\MathInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $math;

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
    $this->database = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $this->math = $this->getMock('\Drupal\currency\Math\MathInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->lineItem = new Basic($configuration, $plugin_id, $plugin_definition, $this->math, $this->stringTranslation, $this->database);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('currency.math', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->math),
      array('database', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->database),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $form = Basic::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\LineItem\Basic', $form);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = array(
      'currency_code' => NULL,
      'name' => NULL,
      'quantity' => 1,
      'amount' => 0,
      'description' => NULL,
    );
    $this->assertEquals($configuration, $this->lineItem->defaultConfiguration());
  }

  /**
   * @covers ::setAmount
   * @covers ::getAmount
   */
  public function testGetAmount() {
    $amount = mt_rand();
    $this->assertSame($this->lineItem, $this->lineItem->setAmount($amount));
    $this->assertSame($amount, $this->lineItem->getAmount());
  }

  /**
   * @covers ::setCurrencyCode
   * @covers ::getCurrencyCode
   */
  public function testGetCurrencyCode() {
    $currency_code = $this->randomMachineName();
    $this->assertSame($this->lineItem, $this->lineItem->setCurrencyCode($currency_code));
    $this->assertSame($currency_code, $this->lineItem->getCurrencyCode());
  }

  /**
   * @covers ::setDescription
   * @covers ::getDescription
   */
  public function testGetDescription() {
    $description = $this->randomMachineName();
    $this->assertSame($this->lineItem, $this->lineItem->setDescription($description));
    $this->assertSame($description, $this->lineItem->getDescription());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_elements = $this->lineItem->buildConfigurationForm($form, $form_state);
    $this->assertInternalType('array', $form_elements);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $amount = mt_rand();
    $currency_code = $this->randomMachineName(3);
    $description = $this->randomMachineName();
    $name = $this->randomMachineName();
    $payment_id = mt_rand();
    $quantity = mt_rand();

    $form = array(
      '#parents' => array('foo', 'bar'),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'amount' => array(
              'amount' => $amount,
              'currency_code' => $currency_code,
            ),
            'description' => $description,
            'name' => $name,
            'payment_id' => $payment_id,
            'quantity' => $quantity,
          ),
        ),
      ));
    $this->lineItem->submitConfigurationForm($form, $form_state);

    $this->assertSame($amount, $this->lineItem->getAmount());
    $this->assertSame($currency_code, $this->lineItem->getCurrencyCode());
    $this->assertSame($description, $this->lineItem->getDescription());
    $this->assertSame($name, $this->lineItem->getName());
    $this->assertSame($quantity, $this->lineItem->getQuantity());
  }

}
