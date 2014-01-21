<?php

/**
 * @file
 * Contains \Drupal\payment\Test\Hook\FieldExtraFieldsUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\payment\Hook\FieldExtraFields;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Hook\FieldExtraFields.
 */
class FieldExtraFieldsUnitTest extends UnitTestCase {

  /**
   * The payment type manager
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentTypeManager;

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\FieldExtraFields.
   */
  protected $service;

  /**
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $translationManager;

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
   * {@inheritdoc
   */
  public function setUp() {
    $this->paymentTypeManager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');

    $this->translationManager = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->service = new FieldExtraFields($this->translationManager, $this->paymentTypeManager);
  }

  /**
   * @covers \Drupal\payment\Hook\FieldExtraFields::invoke()
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
