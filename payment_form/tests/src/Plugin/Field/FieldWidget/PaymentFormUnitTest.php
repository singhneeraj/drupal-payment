<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Field\FieldWidget\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldWidget;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The field widget under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldWidget;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $plugin_id = $this->randomName();
    $plugin_definition = array();
    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $settings = array();
    $this->fieldWidget = $this->getMockBuilder('\Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm')
      ->setConstructorArgs(array($plugin_id, $plugin_definition, $field_definition, $settings))
      ->setMethods(array('formatPlural', 'getSetting'))
      ->getMock();
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummary() {
    $this->fieldWidget->expects($this->once())
      ->method('getSetting')
      ->with('line_items');
    $this->assertInternalType('array', $this->fieldWidget->settingsSummary());
  }

  /**
   * @covers ::formElement
   */
  public function testFormElement() {
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();;
    $delta = 0;
    $element = array();
    $form = array();
    $form_state = array();

    $this->assertInternalType('array', $this->fieldWidget->formElement($items, $delta, $element, $form, $form_state));
  }

  /**
   * @covers ::formElementProcess
   */
  public function testFormElementProcess() {
    $iterator = new \ArrayIterator(array(
      (object) array(
      'plugin_configuration' => array(),
      'plugin_id' => $this->randomName(),
    )
    ));
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->setMethods(array('getIterator'))
      ->getMock();
    $items->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($iterator));

    $element = array(
      '#array_parents' => array('line_items'),
      '#items' => $items,
    );
    $form = array();
    $form_state = array();

    $element = $this->fieldWidget->formElementProcess($element, $form, $form_state);
    $this->assertInternalType('array', $element);
    $this->arrayHasKey('array_parents', $element);
    $this->arrayHasKey('line_items', $element);
  }

}
