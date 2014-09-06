<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_form\Unit\Plugin\Field\FieldType\PaymentFormUnitTest.
 */

namespace Drupal\Tests\payment_form\Unit\Plugin\Field\FieldType;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm
 *
 * @group Payment Form Field
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The Currency form helper.
   *
   * @var \Drupal\currency\FormHelperInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currencyFormHelper;

  /**
   * The field type under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldType;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->currencyFormHelper = $this->getMock('\Drupal\currency\FormHelperInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $container = new Container();
    $container->set('currency.form_helper', $this->currencyFormHelper);
    $container->set('string_translation', $this->stringTranslation);
    \Drupal::setContainer($container);

    $this->fieldType = $this->getMockBuilder('\Drupal\payment_form\Plugin\Field\FieldType\PaymentForm')
      ->disableOriginalConstructor()
      ->setMethods(array('getSetting'))
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
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
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
