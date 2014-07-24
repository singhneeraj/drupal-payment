<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Field\FieldType\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm
 *
 * @group Payment Form Field
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
  protected function setUp() {
    $this->fieldType = $this->getMockBuilder('\Drupal\payment_form\Plugin\Field\FieldType\PaymentForm')
      ->disableOriginalConstructor()
      ->setMethods(array('currencyOptions', 'getSetting', 't'))
      ->getMock();
  }

  /**
   * @covers ::instanceSettingsForm
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
   * @covers ::schema
   */
  public function testSchema() {
    $field_storage_definition = $this->getMock('\Drupal\Core\Field\FieldStorageDefinitionInterface');
    $schema = $this->fieldType->schema($field_storage_definition);
    $this->assertInternalType('array', $schema);
    $this->assertArrayHasKey('plugin_configuration', $schema['columns']);
    $this->assertArrayHasKey('plugin_id', $schema['columns']);
  }

  /**
   * @covers ::getPropertyDefinitions
   */
  public function testGetPropertyDefinitions() {
    $definitions = $this->fieldType->getPropertyDefinitions();
    $this->assertInternalType('array', $definitions);
    $this->assertArrayHasKey('plugin_configuration', $definitions);
    $this->assertArrayHasKey('plugin_id', $definitions);
  }

}
