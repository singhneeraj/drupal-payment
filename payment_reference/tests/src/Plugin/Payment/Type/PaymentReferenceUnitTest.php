<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Plugin\Payment\Type\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Payment\Type;

use Drupal\payment_reference\Plugin\Payment\Type\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The event dispatcher used for testing.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The field instance config used for testing.
   *
   * @var \Drupal\field\Entity\FieldInstanceConfigConfig|\PHPUnit_Framework_MockObject_MockObject
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
   * @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference
   */
  protected $paymentType;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $url_generator = $this->getMockBuilder('\Drupal\Core\Routing\UrlGenerator')
      ->disableOriginalConstructor()
      ->getMock();
    $url_generator->expects($this->any())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

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

    $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentType = new PaymentReference(array(), 'payment_reference', array(), $http_kernel, $request, $this->moduleHandler, $this->eventDispatcher, $url_generator, $field_instance_config_storage);

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($this->payment);
  }

  /**
   * Tests getFieldInstanceConfigId().
   */
  public function testGetFieldInstanceConfigId() {
    $this->payment->expects($this->once())
      ->method('get');
    $this->paymentType->getFieldInstanceConfigId();
  }

  /**
   * Tests setFieldInstanceConfigId().
   */
  public function testSetFieldInstanceConfigId() {
    $map = array(array('payment_reference_field_instance', $this->paymentType));
    $this->payment->expects($this->once())
      ->method('set')
      ->will($this->returnValueMap($map));
    $this->paymentType->setFieldInstanceConfigId($this->randomName());
  }

  /**
   * Tests paymentDescription().
   */
  public function testPaymentDescription() {
    $this->assertSame($this->paymentType->paymentDescription(), $this->fieldInstanceConfig->label());
  }

  /**
   * @covers ::doResumeContext
   */
  public function testResumeContext() {
    $url = 'http://example.com';

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
      ->with(KernelEvents::RESPONSE, new PaymentReferenceUnitTestDoResumeContextCallableConstraint($event, $url), 999);


    $this->paymentType->resumeContext();
  }

}

/**
 * Provides a constraint for the doResumeContext() callable.
 */
class PaymentReferenceUnitTestDoResumeContextCallableConstraint extends \PHPUnit_Framework_Constraint {

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
