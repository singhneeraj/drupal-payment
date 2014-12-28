<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_reference\Unit\Plugin\Field\FieldType\PaymentReferenceItemListUnitTest.
 */

namespace Drupal\Tests\payment_reference\Unit\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList
 *
 * @group Payment Reference Field
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
  protected function setUp() {
    $this->itemList = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList')
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * @covers ::defaultValuesForm
   */
  public function testDefaultValuesForm() {
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    // We explicitly do not want form elements to configure a default value.
    $this->assertSame([], $this->itemList->defaultValuesForm($form, $form_state));
  }

  /**
   * @covers ::defaultValuesFormValidate
   */
  public function testDefaultValuesFormValidate() {
    $element = [];
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    // These methods do nothing, but make sure they do not cause errors either.
    $this->itemList->defaultValuesFormValidate($element, $form, $form_state);
  }

  /**
   * @covers ::defaultValuesFormSubmit
   */
  public function testDefaultValuesFormSubmit() {
    $element = [];
    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    // These methods do nothing, but make sure they do not cause errors either.
    $this->itemList->defaultValuesFormSubmit($element, $form, $form_state);
  }

}
