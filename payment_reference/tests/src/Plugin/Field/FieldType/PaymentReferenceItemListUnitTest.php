<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Plugin\Field\FieldType\PaymentReferenceItemListUnitTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList.
 */
class PaymentReferenceItemListUnitTest extends UnitTestCase {

  /**
   * The field item list.
   *
   * @var \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $itemList;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->itemList = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList')
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * Tests defaultValuesForm().
   */
  public function testDefaultValuesForm() {
    $form = array();
    $form_state = array();
    // We explicitly do not want form elements to configure a default value.
    $this->assertSame(array(), $this->itemList->defaultValuesForm($form, $form_state));
  }

  /**
   * Tests defaultValuesFormValidate().
   */
  public function testDefaultValuesFormValidate() {
    $element = array();
    $form = array();
    $form_state = array();
    // These methods do nothing, but make sure they do not cause errors either.
    $this->itemList->defaultValuesFormSubmit($element, $form, $form_state);
  }

  /**
   * Tests defaultValuesFormSubmit().
   */
  public function testDefaultValuesFormSubmit() {
    $element = array();
    $form = array();
    $form_state = array();
    // These methods do nothing, but make sure they do not cause errors either.
    $this->itemList->defaultValuesFormSubmit($element, $form, $form_state);
  }

}
