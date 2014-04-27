<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Plugin\Field\FieldType\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Field\FieldType;

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
   * The payment queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $queue;
  /**
   * The field's target_id typed data property.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $targetId;

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
    $this->queue = $this->getMock('\Drupal\payment\QueueInterface');

    $this->targetId = $this->getMock('\Drupal\Core\TypedData\TypedDataInterface');

    $this->fieldType = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference')
      ->disableOriginalConstructor()
      ->setMethods(array('currencyOptions', 'get', 'getSetting', 'getPaymentQueue', 't'))
      ->getMock();
    $this->fieldType->expects($this->any())
      ->method('get')
      ->with('target_id')
      ->will($this->returnValue($this->targetId));
    $this->fieldType->expects($this->any())
      ->method('getPaymentQueue')
      ->will($this->returnValue($this->queue));
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

  /**
   * Tests preSave().
   */
  public function testPreSave() {
    $payment_id = mt_rand();
    $acquisition_code = $this->randomName();
    $this->targetId->expects($this->once())
      ->method('getValue')
      ->will($this->returnValue($payment_id));
    $this->queue->expects($this->once())
      ->method('claimPayment')
      ->with($payment_id)
      ->will($this->returnValue($acquisition_code));
    $this->queue->expects($this->once())
      ->method('acquirePayment')
      ->with($payment_id, $acquisition_code);
    $this->fieldType->preSave();
  }

}
