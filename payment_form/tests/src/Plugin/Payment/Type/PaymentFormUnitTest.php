<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Payment\Type\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Payment\Type;

use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Payment\Type\PaymentForm
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The event dispatcher used for testing.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

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
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

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
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->paymentType = new PaymentForm(array(), 'payment_form', array(), $this->moduleHandler, $this->eventDispatcher, $this->entityManager, $this->stringTranslation);

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($this->payment);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_definition = array();
    $plugin_id = $this->randomName();
    $plugin = PaymentForm::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_form\Plugin\Payment\Type\PaymentForm', $plugin);
  }

  /**
   * @covers ::setEntityTypeId
   * @covers ::getEntityTypeId
   */
  public function testGetEntityTypeId() {
    $id = $this->randomName();
    $this->assertSame($this->paymentType, $this->paymentType->setEntityTypeId($id));
    $this->assertSame($id, $this->paymentType->getEntityTypeId());
  }

  /**
   * @covers ::setBundle
   * @covers ::getBundle
   */
  public function testGetBundle() {
    $bundle = $this->randomName();
    $this->assertSame($this->paymentType, $this->paymentType->setBundle($bundle));
    $this->assertSame($bundle, $this->paymentType->getBundle());
  }

  /**
   * @covers ::setFieldName
   * @covers ::getFieldName
   */
  public function testGetFieldName() {
    $name = $this->randomName();
    $this->assertSame($this->paymentType, $this->paymentType->setFieldName($name));
    $this->assertSame($name, $this->paymentType->getFieldName());
  }

  /**
   * @covers ::paymentDescription
   *
   * @depends testGetEntityTypeId
   * @depends testGetBundle
   * @depends testGetFieldName
   */
  public function testPaymentDescription() {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();
    $label = $this->randomName();
    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->atLeastOnce())
      ->method('getLabel')
      ->will($this->returnValue($label));

    $definitions = array(
      $field_name => $field_definition,
    );

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($definitions));

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);
    $this->paymentType->setFieldName($field_name);

    $this->assertSame($label, $this->paymentType->paymentDescription());
  }

  /**
   * @covers ::paymentDescription
   */
  public function testPaymentDescriptionWithNonExistingField() {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue(array()));

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);

    $this->assertSame('Unavailable', $this->paymentType->paymentDescription());
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