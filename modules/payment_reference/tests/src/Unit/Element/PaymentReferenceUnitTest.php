<?php

/**
 * @file
 * Contains \Drupal\Tests\payment_reference\Unit\Element\PaymentReferenceUnitTest.
 */

namespace Drupal\Tests\payment_reference\Unit\Element;

use Drupal\Component\Utility\Random;
use Drupal\payment_reference\Element\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_reference\Element\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The element under test.
   *
   * @var \Drupal\payment_reference\Element\PaymentReference
   */
  protected $element;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $dateFormatter;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $linkGenerator;

  /**
   * The payment method selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelectorManager;

  /**
   * The payment queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentQueue;

  /**
   * The payment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStorage;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The temporary payment storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $temporaryPaymentStorage;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->dateFormatter = $this->getMockBuilder('\Drupal\Core\Datetime\DateFormatter')
      ->disableOriginalConstructor()
      ->getMock();

    $this->linkGenerator = $this->getMock('\Drupal\Core\Utility\LinkGeneratorInterface');

    $this->paymentMethodSelectorManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface');

    $this->paymentQueue = $this->getMock('\Drupal\payment\QueueInterface');

    $this->paymentStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->temporaryPaymentStorage = $this->getMock('\Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface');

    $this->urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $this->element = new PaymentReference($configuration, $plugin_id, $plugin_definition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->paymentMethodSelectorManager, new Random(), $this->temporaryPaymentStorage, $this->paymentQueue);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment')
      ->willReturn($this->paymentStorage);

    $key_value_expirable = $this->getMock('\Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface');
    $key_value_expirable->expects($this->once())
      ->method('get')
      ->with('payment.payment_type.payment_reference')
      ->willReturn($this->temporaryPaymentStorage);

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('date.formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->dateFormatter),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('keyvalue.expirable', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $key_value_expirable),
      array('link_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->linkGenerator),
      array('payment_reference.queue', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentQueue),
      array('plugin.manager.payment.method_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodSelectorManager),
      array('request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();

    $form = PaymentReference::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_reference\Element\PaymentReference', $form);
  }

  /**
   * @covers ::getPaymentQueue
   */
  public function testGetPaymentQueue() {
    $method = new \ReflectionMethod($this->element, 'getPaymentQueue');
    $method->setAccessible(TRUE);
    $this->assertSame($this->paymentQueue, $method->invoke($this->element));
  }

  /**
   * @covers ::getTemporaryPaymentStorage
   */
  public function testGetTemporaryPaymentStorage() {
    $method = new \ReflectionMethod($this->element, 'getTemporaryPaymentStorage');
    $method->setAccessible(TRUE);
    $this->assertSame($this->temporaryPaymentStorage, $method->invoke($this->element));
  }

}
