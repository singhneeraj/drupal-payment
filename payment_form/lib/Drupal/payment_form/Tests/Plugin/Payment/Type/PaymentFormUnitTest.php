<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Test\Plugin\Payment\Type\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Test\Plugin\Payment\Type;

use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests \Drupal\payment_form\Plugin\Payment\Type\PaymentForm.
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The event dispatcher used for testing.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The field instance used for testing.
   *
   * @var \Drupal\field\Entity\FieldInstance|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldInstance;

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The payment used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment type plugin under test.
   *
   * @var \Drupal\payment_form\Plugin\Payment\Type\PaymentForm
   */
  protected $paymentType;

  /**
   * The URL generator used for testing.
   *
   * @var \Drupal\Core\Routing\UrlGenerator|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\Payment\Type\PaymentForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->urlGenerator = $this->getMockBuilder('\Drupal\Core\Routing\UrlGenerator')
      ->disableOriginalConstructor()
      ->getMock();
    $this->urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

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

    $this->paymentType = new PaymentForm(array(), 'payment_form', array(), $http_kernel, $this->eventDispatcher, $request, $this->moduleHandler, $this->urlGenerator, $field_instance_storage);

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
      ->method('get')
      ->with('payment_form_field_instance');
    $this->paymentType->getFieldInstanceId();
  }

  /**
   * Tests setFieldInstanceId().
   */
  public function testSetFieldInstanceId() {
    $this->payment->expects($this->once())
      ->method('set')
      ->with('payment_form_field_instance')
      ->will($this->returnValue($this->paymentType));
    $this->assertSame(spl_object_hash($this->paymentType), spl_object_hash($this->paymentType->setFieldInstanceId($this->randomName())));
  }

  /**
   * Tests paymentDescription().
   */
  public function testPaymentDescription() {
    $this->assertSame($this->paymentType->paymentDescription(), $this->fieldInstance->label());
  }

  /**
   * Tests resumeContext().
   */
  public function testResumeContext() {
    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

    $this->eventDispatcher->expects($this->once())
      ->method('addListener')
      ->with(KernelEvents::RESPONSE)
      ->will($this->returnValue('http://example.com'));

    $this->paymentType->resumeContext();
  }

}
