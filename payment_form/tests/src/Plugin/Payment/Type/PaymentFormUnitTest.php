<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Payment\Type\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Payment\Type;

use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Payment\Type\PaymentForm
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The event dispatcher used for testing.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The field instance config used for testing.
   *
   * @var \Drupal\field\Entity\FieldInstanceConfig|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldInstanceConfig;

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
    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->fieldInstanceConfig = $this->getMockBuilder('\Drupal\field\Entity\FieldInstanceConfig')
      ->disableOriginalConstructor()
      ->getMock();
    $this->fieldInstanceConfig->expects($this->any())
      ->method('label')
      ->will($this->returnValue($this->randomName()));

    $field_instance_config_storage = $this->getMockBuilder('\Drupal\field\FieldInstanceConfigStorage')
      ->disableOriginalConstructor()
      ->getMock();
    $field_instance_config_storage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($this->fieldInstanceConfig));

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $http_kernel = $this->getMockBuilder('\Drupal\Core\HttpKernel')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentType = new PaymentForm(array(), 'payment_form', array(), $http_kernel, $this->moduleHandler, $this->eventDispatcher, $field_instance_config_storage);

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($this->payment);
  }

  /**
   * @covers ::getFieldInstanceConfigId
   */
  public function testGetFieldInstanceConfigId() {
    $this->payment->expects($this->once())
      ->method('get')
      ->with('payment_form_field_instance');
    $this->paymentType->getFieldInstanceConfigId();
  }

  /**
   * @covers ::setFieldInstanceConfigId
   */
  public function testSetFieldInstanceConfigId() {
    $this->payment->expects($this->once())
      ->method('set')
      ->with('payment_form_field_instance')
      ->will($this->returnValue($this->paymentType));
    $this->assertSame(spl_object_hash($this->paymentType), spl_object_hash($this->paymentType->setFieldInstanceConfigId($this->randomName())));
  }

  /**
   * @covers ::paymentDescription
   */
  public function testPaymentDescription() {
    $this->assertSame($this->paymentType->paymentDescription(), $this->fieldInstanceConfig->label());
  }

  /**
   * @covers ::setDestinationUrl
   * @covers ::getDestinationUrl
   */
  public function testGetDestinationUrl() {
    $destination_url = $this->randomName();
    $this->assertSame(spl_object_hash($this->paymentType), spl_object_hash($this->paymentType->setDestinationUrl($destination_url)));
    $this->assertSame($destination_url, $this->paymentType->getDestinationUrl());
  }

  /**
   * @covers ::doResumeContext
   * @depends testGetDestinationUrl
   */
  public function testResumeContext() {
    $url = 'http://example.com';

    $this->paymentType->setDestinationUrl($url);

    $kernel = $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface');
    $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();
    $request_type = $this->randomName();
    $response = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
      ->disableOriginalConstructor()
      ->getMock();
    $event = new FilterResponseEvent($kernel, $request, $request_type, $response);

    $this->eventDispatcher->expects($this->once())
      ->method('addListener')
      ->with(KernelEvents::RESPONSE, new PaymentFormUnitTestDoResumeContextCallableConstraint($event, $url), 999);


    $this->paymentType->resumeContext();
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->paymentType->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('destination_url', $configuration);
    $this->assertInternalType('null', $configuration['destination_url']);
  }

}

/**
 * Provides a constraint for the doResumeContext() callable.
 */
class PaymentFormUnitTestDoResumeContextCallableConstraint extends \PHPUnit_Framework_Constraint {

  /**
   * The event to listen to.
   *
   * @var \Symfony\Component\HttpKernel\Event\FilterResponseEvent
   */
  protected $event;

  /**
   * The redirect URL.
   *
   * @var string
   */
  protected $url;

  /**
   * Constructs a new class instance.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   * @param string $url
   */
  public function __construct(FilterResponseEvent $event, $url) {
    $this->event = $event;
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function matches($other) {
    if (is_callable($other)) {
      $other($this->event);
      $response = $this->event->getResponse();
      if ($response instanceof RedirectResponse) {
        return $response->getTargetUrl() == $this->url;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function toString() {
    return 'returns a RedirectResponse through a KernelEvents::RESPONSE event listener';
  }

}