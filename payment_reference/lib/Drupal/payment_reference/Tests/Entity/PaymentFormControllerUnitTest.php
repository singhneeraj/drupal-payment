<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Test\Entity\PaymentFormControllerUnitTest.
 */

namespace Drupal\payment_reference\Test\Entity;

use Drupal\payment_reference\Entity\PaymentFormController;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_reference\Entity\PaymentFormController.
 */
class PaymentFormControllerUnitTest extends UnitTestCase {

  /**
   * The form under test
   *
   * @var \Drupal\payment_reference\Entity\PaymentFormController
   */
  protected $form;

  /**
   * A payment entity used for testing.
   *
   * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Entity\PaymentFormController unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->form = new PaymentFormController();
    $this->form->setEntity($this->payment);
  }

  /**
   * Tests form().
   */
  public function testForm() {
    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $this->payment->expects($this->any())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $form = array();
    $form_state = array();
    $build = $this->form->form($form, $form_state);
    $this->assertSame(spl_object_hash($this->payment), spl_object_hash($build['line_items']['#payment']));
    $this->assertNotSame(spl_object_hash($this->payment), spl_object_hash($build['payment_method']['#default_value']));
    $this->assertInstanceOf('\Drupal\payment\Entity\PaymentInterface', $build['payment_method']['#default_value']);
  }

  /**
   * Tests buildEntity().
   */
  public function testBuildEntity() {
    $form = array();
    $form_state = array();
    $this->assertInstanceOf('\Drupal\payment\Entity\PaymentInterface', $this->form->buildEntity($form, $form_state));
  }

}
