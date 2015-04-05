<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\Payment\PaymentListBuilderUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment {

  use Drupal\Core\Url;
  use Drupal\payment\Entity\Payment\PaymentListBuilder;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentListBuilder
   *
   * @group Payment
   */
  class PaymentListBuilderUnitTest extends UnitTestCase {

    /**
     * The currency storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyStorage;

    /**
     * The date formatter.
     *
     * @var \Drupal\Core\Datetime\DateFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateFormatter;

    /**
     * The entity storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorage;

    /**
     * The entity type.
     *
     * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityType;

    /**
     * The list builder under test.
     *
     * @var \Drupal\payment\Entity\Payment\PaymentListBuilder
     */
    protected $listBuilder;

    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleHandler;

    /**
     * The redirect destination.
     *
     * @var \Drupal\Core\Routing\RedirectDestinationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectDestination;

    /**
     * The string translation service.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->currencyStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

      $this->dateFormatter = $this->getMockBuilder('\Drupal\Core\Datetime\DateFormatter')
        ->disableOriginalConstructor()
        ->getMock();

      $this->entityStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

      $this->entityType = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');

      $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

      $this->redirectDestination = $this->getMock('\Drupal\Core\Routing\RedirectDestinationInterface');

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->listBuilder = new PaymentListBuilder($this->entityType, $this->entityStorage, $this->stringTranslation, $this->moduleHandler, $this->redirectDestination, $this->dateFormatter, $this->currencyStorage);
    }

    /**
     * @covers ::createInstance
     * @covers ::__construct
     */
    function testCreateInstance() {
      $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
      $map = array(
        array('currency', $this->currencyStorage),
        array('payment', $this->entityStorage),
      );
      $entity_manager->expects($this->exactly(2))
        ->method('getStorage')
        ->will($this->returnValueMap($map));

      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = array(
        array('date.formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->dateFormatter),
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
        array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
        array('redirect.destination', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->redirectDestination),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->will($this->returnValueMap($map));

      $form = PaymentListBuilder::createInstance($container, $this->entityType);
      $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentListBuilder', $form);
    }

    /**
     * @covers ::buildHeader
     */
    function testBuildHeader() {
      $header = $this->listBuilder->buildHeader();
      $expected = array(
        'updated' => [
          'data' => 'Last updated',
          'field' => 'changed',
          'sort' => 'DESC',
          'specifier' => 'changed',
        ],
        'status' => 'Status',
        'amount' => 'Amount',
        'payment_method' => array(
          'data' => 'Payment method',
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        'owner' => array(
          'data' => 'Payer',
          'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
        ),
        'operations' => 'Operations',
      );
      $this->assertSame($expected, $header);
    }

    /**
     * @covers ::buildOperations
     */
    public function testBuildOperations() {
      $this->moduleHandler->expects($this->any())
        ->method('invokeAll')
        ->will($this->returnValue([]));

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $expected_build = array(
        '#type' => 'operations',
        '#links' => [],
        '#attached' => array(
          'library' => array('core/drupal.ajax'),
        )
      );
      $this->assertSame($expected_build, $this->listBuilder->buildOperations($payment));
    }

    /**
     * @covers ::buildRow
     *
     * @dataProvider providerTestBuildRow
     *
     * @depends testBuildOperations
     */
    function testBuildRow($payment_currency_exists) {
      $payment_changed_time = time();
      $payment_changed_time_formatted = $this->randomMachineName();
      $payment_currency_code = $this->randomMachineName();
      $payment_amount = mt_rand();
      $payment_amount_formatted = $this->randomMachineName();

      $payment_status_definition = array(
        'label' => $this->randomMachineName(),
      );

      $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
      $payment_status->expects($this->any())
        ->method('getPluginDefinition')
        ->will($this->returnValue($payment_status_definition));

      $owner = $this->getMockBuilder('\Drupal\user\Entity\User')
        ->disableOriginalConstructor()
        ->getMock();

      $payment_method_label = $this->randomMachineName();
      $payment_method_definition = [
        'label' => $payment_method_label,
      ];
      $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
      $payment_method->expects($this->atLeastOnce())
        ->method('getPluginDefinition')
        ->willReturn($payment_method_definition);

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment->expects($this->any())
        ->method('getAmount')
        ->will($this->returnValue($payment_amount));
      $payment->expects($this->any())
        ->method('getChangedTime')
        ->will($this->returnValue($payment_changed_time));
      $payment->expects($this->any())
        ->method('getCurrencyCode')
        ->will($this->returnValue($payment_currency_code));
      $payment->expects($this->any())
        ->method('getOwner')
        ->will($this->returnValue($owner));
      $payment->expects($this->any())
        ->method('getPaymentMethod')
        ->will($this->returnValue($payment_method));
      $payment->expects($this->any())
        ->method('getPaymentStatus')
        ->will($this->returnValue($payment_status));

      $currency = $this->getMockBuilder('\Drupal\currency\Entity\Currency')
        ->disableOriginalConstructor()
        ->getMock();
      $currency->expects($this->once())
        ->method('formatAmount')
        ->with($payment_amount)
        ->will($this->returnValue($payment_amount_formatted));

      $map = array(
        array($payment_currency_code, $payment_currency_exists ? $currency : NULL),
        array('XXX', $payment_currency_exists ? NULL : $currency),
      );
      $this->currencyStorage->expects($this->atLeastOnce())
        ->method('load')
        ->will($this->returnValueMap($map));

      $this->dateFormatter->expects($this->once())
        ->method('format')
        ->with($payment_changed_time)
        ->will($this->returnValue($payment_changed_time_formatted));

      $this->moduleHandler->expects($this->any())
        ->method('invokeAll')
        ->will($this->returnValue([]));

      $build = $this->listBuilder->buildRow($payment);
      unset($build['data']['operations']['data']['#attached']);
      $expected_build = array(
        'data' => array(
          'updated' => $payment_changed_time_formatted,
          'status' => $payment_status_definition['label'],
          'amount' => $payment_amount_formatted,
          'payment_method' => $payment_method_label,
          'owner' => array(
            'data' => array(
              '#theme' => 'username',
              '#account' => $owner,
            )
          ),
          'operations' => array(
            'data' => array(
              '#type' => 'operations',
              '#links' => [],
            ),
          ),
        ),
      );
      $this->assertSame($expected_build, $build);
    }

    /**
     * Provides data to self::testBuildRow().
     */
    public function providerTestBuildRow() {
      return array(
        array(TRUE),
        array(FALSE),
      );
    }

    /**
     * @covers ::load
     * @covers ::render
     * @covers ::getEntityIds
     *
     * @depends testBuildHeader
     */
    public function testRender() {
      $query = $this->getMock('\Drupal\Core\Entity\Query\QueryInterface');
      $query->expects($this->atLeastOnce())
        ->method('pager')
        ->willReturnSelf();

      $this->entityStorage->expects($this->atLeastOnce())
        ->method('getQuery')
        ->willReturn($query);
      $this->entityStorage->expects($this->once())
        ->method('loadMultiple')
        ->will($this->returnValue([]));

      $build = $this->listBuilder->render();
      unset($build['table']['#header']);
      $expected_build = array(
        '#type' => 'table',
        '#title' => NULL,
        '#rows' => [],
        '#empty' => 'There are no payments yet.',
        '#cache' => [
          'contexts' => NULL,
        ],
      );
      $this->assertEquals($expected_build, $build['table']);
    }

    /**
     * @covers ::getDefaultOperations
     */
    public function testGetDefaultOperationsWithoutAccess() {
      $method = new \ReflectionMethod($this->listBuilder, 'getDefaultOperations');
      $method->setAccessible(TRUE);

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $operations = $method->invoke($this->listBuilder, $payment);
      $this->assertEmpty($operations);
    }

    /**
     * @covers ::getDefaultOperations
     */
    public function testGetDefaultOperationsWithAccess() {
      $method = new \ReflectionMethod($this->listBuilder, 'getDefaultOperations');
      $method->setAccessible(TRUE);

      $url_canonical = new Url($this->randomMachineName());
      $url_edit_form = new Url($this->randomMachineName());
      $url_delete_form = new Url($this->randomMachineName());
      $url_update_status_form = new Url($this->randomMachineName());
      $url_capture_form = new Url($this->randomMachineName());
      $url_refund_form = new Url($this->randomMachineName());
      $url_complete = new Url($this->randomMachineName());

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $map = [
        ['view', NULL, FALSE, TRUE],
        ['update', NULL, FALSE, TRUE],
        ['delete', NULL, FALSE, TRUE],
        ['update_status', NULL, FALSE, TRUE],
        ['capture', NULL, FALSE, TRUE],
        ['refund', NULL, FALSE, TRUE],
        ['complete', NULL, FALSE, TRUE],
      ];
      $payment->expects($this->atLeast(count($map)))
        ->method('access')
        ->willReturnMap($map);
      $payment->expects($this->any())
        ->method('hasLinkTemplate')
        ->willReturn(TRUE);
      $map = [
        ['canonical', [], $url_canonical],
        ['edit-form', [], $url_edit_form],
        ['delete-form', [], $url_delete_form],
        ['update-status-form', [], $url_update_status_form],
        ['capture-form', [], $url_capture_form],
        ['refund-form', [], $url_refund_form],
        ['complete', [], $url_complete],
      ];
      $payment->expects($this->atLeast(count($map)))
        ->method('urlInfo')
        ->willReturnMap($map);

      $destination = $this->randomMachineName();

      $this->redirectDestination->expects($this->atLeastOnce())
        ->method('get')
        ->willReturn($destination);

      $operations = $method->invoke($this->listBuilder, $payment);
      $expected_operations = array(
        'view' => array(
          'title' => 'View',
          'weight' => -10,
        ),
        'edit' => array(
          'title' => 'Edit',
          'weight' => 10,
          'query' => array(
            'destination' => $destination,
          ),
        ),
        'delete' => array(
          'title' => 'Delete',
          'weight' => 100,
          'query' => array(
            'destination' => $destination,
          ),
        ),
        'update_status' => array(
          'title' => 'Update status',
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-accepts' => 'application/vnd.drupal-modal',
          ),
          'query' => array(
            'destination' => $destination,
          ),
        ),
        'capture' => array(
          'title' => 'Capture',
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-accepts' => 'application/vnd.drupal-modal',
          ),
          'query' => array(
            'destination' => $destination,
          ),
        ),
        'refund' => array(
          'title' => 'Refund',
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-accepts' => 'application/vnd.drupal-modal',
          ),
          'query' => array(
            'destination' => $destination,
          ),
        ),
        'complete' => array(
          'title' => 'Complete',
        ),
      );
      $this->assertEmpty(array_diff_key($expected_operations, $operations));
      $this->assertEmpty(array_diff_key($operations, $expected_operations));
      foreach ($operations as $name => $operation) {
        $this->assertInstanceof('\Drupal\Core\Url', $operation['url']);
        unset($operation['url']);
        $this->assertSame($expected_operations[$name], $operation);
      }
    }

  }

}

namespace {

  if (!defined('RESPONSIVE_PRIORITY_LOW')) {
    define('RESPONSIVE_PRIORITY_LOW', 'priority-low');
  }
  if (!defined('RESPONSIVE_PRIORITY_MEDIUM')) {
    define('RESPONSIVE_PRIORITY_MEDIUM', 'priority-medium');
  }

}
