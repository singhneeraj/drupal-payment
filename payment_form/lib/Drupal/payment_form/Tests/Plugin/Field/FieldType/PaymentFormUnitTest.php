<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Field\FieldType\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm.
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The field type under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldType;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\Field\FieldType\PaymentForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->fieldType = $this->getMockBuilder('\Drupal\payment_form\Plugin\Field\FieldType\PaymentForm')
      ->disableOriginalConstructor()
      ->setMethods(array('currencyOptions', 'getSetting', 't'))
      ->getMock();
  }

  /**
   * Tests instanceSettingsForm().
   */
  public function testInstanceSettingsForm() {
    $this->fieldType->expects($this->once())
      ->method('getSetting')
      ->with('currency_code');
    $form = array();
    $form_state = array();
    $this->assertInternalType('array', $this->fieldType->instanceSettingsForm($form, $form_state));
  }

  /**
   * Tests schema().
   */
  public function testSchema() {
    $field = $this->getMock('\Drupal\field\FieldConfigInterface');
    $schema = $this->fieldType->schema($field);
    $this->assertInternalType('array', $schema);
    $this->assertArrayHasKey('plugin_configuration', $schema['columns']);
    $this->assertArrayHasKey('plugin_id', $schema['columns']);
  }

  /**
   * Tests getPropertyDefinitions().
   */
  public function testGetPropertyDefinitions() {
    $definitions = $this->fieldType->getPropertyDefinitions();
    $this->assertInternalType('array', $definitions);
    $this->assertArrayHasKey('plugin_configuration', $definitions);
    $this->assertArrayHasKey('plugin_id', $definitions);
  }

}
