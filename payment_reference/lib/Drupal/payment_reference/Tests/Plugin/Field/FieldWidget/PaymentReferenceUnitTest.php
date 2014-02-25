<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Test\Plugin\Field\FieldWidget\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Test\Plugin\Field\FieldWidget;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference.
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * A user account used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * A field instance used for testing.
   *
   * @var \Drupal\field\FieldInstanceConfigInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldInstanceConfig;

  /**
   * The field widget plugin under test.
   *
   * @var \Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $widget;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->fieldInstanceConfig = $this->getMock('\Drupal\field\FieldInstanceConfigInterface');

    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->widget = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference')
      ->setConstructorArgs(array($this->randomName(), array(), $this->fieldInstanceConfig, array(), $this->currentUser))
      ->setMethods(array('getFieldSetting'))
      ->getMock();
  }

  /**
   * Tests formElement().
   */
  public function testFormElement() {
    $this->fieldInstanceConfig->expects($this->once())
      ->method('isRequired')
      ->will($this->returnValue(TRUE));

    $user_id = 2;
    $this->currentUser->expects($this->exactly(1))
      ->method('id')
      ->will($this->returnValue($user_id));

    $currency_code = 'EUR';
    $line_items_data = array(
      array(
        'plugin_configuration' => array(),
        'plugin_id' => $this->randomName(),
      ),
    );
    $map = array(
      array('currency_code', $currency_code),
      array('line_items_data', $line_items_data),
    );
    $this->widget->expects($this->exactly(2))
      ->method('getFieldSetting')
      ->will($this->returnValueMap($map));

    $items = $this->getMockBuilder('\Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();

    $form = array();
    $form_state = array();
    $element = $this->widget->formElement($items, 3, array(), $form, $form_state);
    $this->assertSame($element['payment_id']['#owner_id'], $user_id);
    $this->assertSame($element['payment_id']['#payment_currency_code'], $currency_code);
    $this->assertSame($element['payment_id']['#payment_line_items_data'], $line_items_data);
  }

}
