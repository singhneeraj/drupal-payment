<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Test\Plugin\payment\type\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Test\Plugin\payment\type;

use Drupal\payment_form\Plugin\payment\type\PaymentForm;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_form\Plugin\payment\type\PaymentForm.
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The field instance used for testing.
   *
   * @var \Drupal\field\Entity\FieldInstance
   */
  protected $fieldInstance;

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The payment used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * The payment type plugin under test.
   *
   * @var \Drupal\payment_form\Plugin\payment\type\PaymentForm
   */
  protected $paymentType;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\payment\type\PaymentForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $url_generator = $this->getMockBuilder('\Drupal\Core\Routing\UrlGenerator')
      ->disableOriginalConstructor()
      ->getMock();
    $url_generator->expects($this->any())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

    $this->fieldInstance = $this->getMockBuilder('\Drupal\field\Entity\FieldInstance')
      ->disableOriginalConstructor()
      ->getMock();
    $this->fieldInstance->expects($this->any())
      ->method('label')
      ->will($this->returnValue($this->randomName()));

    $field_instance_storage = $this->getMockBuilder('\Drupal\field\FieldInstanceStorageController')
      ->disableOriginalConstructor()
      ->getMock();
    $field_instance_storage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($this->fieldInstance));

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandler');

    $http_kernel = $this->getMockBuilder('\Drupal\Core\HttpKernel')
      ->disableOriginalConstructor()
      ->getMock();

    $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentType = new PaymentForm(array(), 'payment_form', array(), $http_kernel, $request, $this->moduleHandler, $url_generator, $field_instance_storage);

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($this->payment);
  }

  /**
   * Tests getFieldInstanceId().
   */
  public function testGetFieldInstanceId() {
    $this->payment->expects($this->once())
      ->method('get');
    $this->paymentType->getFieldInstanceId();
  }

  /**
   * Tests setFieldInstanceId().
   */
  public function testSetFieldInstanceId() {
    $map = array(array('payment_form_field_instance', $this->paymentType));
    $this->payment->expects($this->once())
      ->method('set')
      ->will($this->returnValueMap($map));
    $this->paymentType->setFieldInstanceId($this->randomName());
  }

  /**
   * Tests paymentDescription().
   */
  public function testPaymentDescription() {
    $this->assertSame($this->paymentType->paymentDescription(), $this->fieldInstance->label());
  }

}
