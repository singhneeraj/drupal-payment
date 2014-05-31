<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Hook\EntityExtraFieldInfoUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\payment\Hook\EntityExtraFieldInfo;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\EntityExtraFieldInfo
 */
class EntityExtraFieldInfoUnitTest extends UnitTestCase {

  /**
   * The payment type manager used for testing.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentTypeManager;

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\EntityExtraFieldInfo
   */
  protected $service;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Hook\FieldExtraFields unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->paymentTypeManager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->service = new EntityExtraFieldInfo($this->stringTranslation, $this->paymentTypeManager);
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $payment_type_plugin_id = $this->randomName();
    $payment_type_definitions = array(
      $payment_type_plugin_id => array(),
    );
    $this->paymentTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($payment_type_definitions));

    $fields = $this->service->invoke();
    $this->assertInternalType('array', $fields);
    $this->assertArrayHasKey('payment', $fields);
    $this->assertInternalType('array', $fields['payment']);
    $this->arrayHasKey($payment_type_plugin_id, $fields['payment']);
    $this->assertInternalType('array', $fields['payment'][$payment_type_plugin_id]);
    foreach ($fields['payment'][$payment_type_plugin_id] as $field_definition) {
      $this->assertInternalType('array', $field_definition);
      $this->arrayHasKey('label', $field_definition);
    }
  }
}
