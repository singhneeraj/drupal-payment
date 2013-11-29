<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Test\Plugin\Field\FieldType\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Test\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference.
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The field item list.
   *
   * @var \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference
   */
  protected $fieldType;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->fieldType = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference')
      ->disableOriginalConstructor()
      ->setMethods(array('currencyOptions', 'getFieldSetting', 't'))
      ->getMock();
  }

  /**
   * Tests instanceSettingsForm().
   */
  public function testInstanceSettingsForm() {
    $form = array();
    $form_state = array();
    $form = $this->fieldType->instanceSettingsForm($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->arrayHasKey('currency_code', $form);
    $this->arrayHasKey('line_items', $form);
  }

  /**
   * Tests settingsForm().
   */
  public function testSettingsForm() {
    $form = array();
    $form_state = array();
    $has_data = TRUE;
    $this->assertSame(array(), $this->fieldType->settingsForm($form, $form_state, $has_data));
  }

}
