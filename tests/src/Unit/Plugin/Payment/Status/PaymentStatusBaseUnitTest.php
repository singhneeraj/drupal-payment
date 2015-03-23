<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Status\PaymentStatusBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Status {

  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase
   *
   * @group Payment
   */
  class PaymentStatusBaseUnitTest extends UnitTestCase {

    /**
     * The default datetime.
     *
     * @var \Drupal\Core\Datetime\DrupalDateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultDateTime;

    /**
     * The language manager.
     *
     * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $languageManager;

    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleHandler;

    /**
     * The payment status plugin manager used for testing.
     *
     * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $paymentStatusManager;

    /**
     * The definition of the payment status under test.
     *
     * @var array
     */
    public $pluginDefinition;

    /**
     * The ID of the payment status under test.
     *
     * @var string
     */
    public $pluginId;

    /**
     * The payment status under test.
     *
     * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase|\PHPUnit_Framework_MockObject_MockObject
     */
    public $status;

    /**
     * {@inheritdoc}
     */
    public function setup() {
      $this->defaultDateTime = $this->getMockBuilder('\Drupal\Core\Datetime\DrupalDateTime')
        ->disableOriginalConstructor()
        ->getMock();

      $language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

      $this->languageManager = $this->getMock('\Drupal\Core\Language\LanguageManagerInterface');
      $this->languageManager->expects($this->any())
        ->method('getCurrentLanguage')
        ->will($this->returnValue($language));

      $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

      $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

      $configuration = [];
      $this->pluginId = $this->randomMachineName();
      $this->pluginDefinition = array(
        'label' => $this->randomMachineName(),
      );
      $this->status = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Status\PaymentStatusBase')
        ->setConstructorArgs(array(
          $configuration,
          $this->pluginId,
          $this->pluginDefinition,
          $this->moduleHandler,
          $this->paymentStatusManager,
          $this->defaultDateTime
        ))
        ->getMockForAbstractClass();
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreate() {
      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      \Drupal::setContainer($container);
      $map = array(
        array(
          'language_manager',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->languageManager
        ),
        array(
          'module_handler',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->moduleHandler
        ),
        array(
          'plugin.manager.payment.status',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->paymentStatusManager
        ),
      );
      $container->expects($this->any())
        ->method('get')
        ->will($this->returnValueMap($map));

      /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase $class_name */
      $class_name = get_class($this->status);

      $line_item = $class_name::create($container, [],
        $this->randomMachineName(), []);
      $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\Status\PaymentStatusBase',
        $line_item);
    }

    /**
     * @covers ::calculateDependencies
     */
    public function testCalculateDependencies() {
      $this->assertSame([], $this->status->calculateDependencies());
    }

    /**
     * @covers ::defaultConfiguration
     */
    public function testDefaultConfiguration() {
      $expected_configuration = array(
        'created' => time(),
        'id' => 0,
      );
      $this->assertSame($expected_configuration,
        $this->status->defaultConfiguration());
    }

    /**
     * @covers ::setConfiguration
     * @covers ::getConfiguration
     */
    public function testGetConfiguration() {
      $configuration = array(
          $this->randomMachineName() => mt_rand(),
        ) + $this->status->defaultConfiguration();
      $this->assertNull($this->status->setConfiguration($configuration));
      $this->assertSame($configuration, $this->status->getConfiguration());
    }

    /**
     * @covers ::setCreated
     * @covers ::getCreated
     */
    public function testGetCreated() {
      $created = mt_rand();
      $this->assertSame($this->status, $this->status->setCreated($created));
      $this->assertSame($created, $this->status->getCreated());
    }

    /**
     * @covers ::setPayment
     * @covers ::getPayment
     */
    public function testGetPayment() {
      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $this->assertSame($this->status, $this->status->setPayment($payment));
      $this->assertSame($payment, $this->status->getPayment());
    }

    /**
     * @covers ::getChildren
     */
    public function testGetChildren() {
      $children = array($this->randomMachineName());
      $this->paymentStatusManager->expects($this->once())
        ->method('getChildren')
        ->with($this->pluginId)
        ->will($this->returnValue($children));
      $this->assertSame($children, $this->status->getChildren());
    }

    /**
     * @covers ::getDescendants
     */
    public function testGetDescendants() {
      $descendants = array($this->randomMachineName());
      $this->paymentStatusManager->expects($this->once())
        ->method('getDescendants')
        ->with($this->pluginId)
        ->will($this->returnValue($descendants));
      $this->assertSame($descendants, $this->status->getDescendants());
    }

    /**
     * @covers ::getAncestors
     */
    public function testGetAncestors() {
      $ancestors = array($this->randomMachineName());
      $this->paymentStatusManager->expects($this->once())
        ->method('getAncestors')
        ->with($this->pluginId)
        ->will($this->returnValue($ancestors));
      $this->assertSame($ancestors, $this->status->getAncestors());
    }

    /**
     * @covers ::hasAncestor
     */
    public function testHasAncestor() {
      $expected = TRUE;
      $this->paymentStatusManager->expects($this->once())
        ->method('hasAncestor')
        ->with($this->pluginId)
        ->will($this->returnValue($expected));
      $this->assertSame($expected, $this->status->hasAncestor($this->pluginId));
    }

    /**
     * @covers ::isOrHasAncestor
     */
    public function testIsOrHasAncestor() {
      $expected = TRUE;
      $this->paymentStatusManager->expects($this->once())
        ->method('isOrHasAncestor')
        ->with($this->pluginId)
        ->will($this->returnValue($expected));
      $this->assertSame($expected,
        $this->status->isOrHasAncestor($this->pluginId));
    }

  }

}

namespace {

  if (!function_exists('drupal_get_user_timezone')) {
    function drupal_get_user_timezone() {
    }
  }

}
