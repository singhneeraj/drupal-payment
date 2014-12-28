<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Element\PaymentReferenceBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Element {

  use Drupal\Component\Utility\Random;
  use Drupal\Core\Form\FormState;
  use Drupal\Tests\UnitTestCase;

  /**
   * @coversDefaultClass \Drupal\payment\Element\PaymentReferenceBase
   *
   * @group Payment Reference Field
   */
  class PaymentReferenceBaseUnitTest extends UnitTestCase {

    /**
     * The element under test.
     *
     * @var \Drupal\payment\Element\PaymentReferenceBase|\PHPUnit_Framework_MockObject_MockObject
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
     * The plugin definition.
     *
     * @var array
     */
    protected $pluginDefinition = [];

    /**
     * The renderer.
     *
     * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderer;

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

      $this->renderer = $this->getMock('\Drupal\Core\Render\RendererInterface');

      $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
        ->disableOriginalConstructor()
        ->getMock();

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->temporaryPaymentStorage = $this->getMock('\Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface');

      $this->urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');

      $configuration = [];
      $plugin_id = $this->randomMachineName();

      $this->pluginDefinition['class'] = $this->randomMachineName();

      $this->element = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->setConstructorArgs(array($configuration, $plugin_id, $this->pluginDefinition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random()))
        ->getMockForAbstractClass();
      $this->element->expects($this->any())
        ->method('getPaymentQueue')
        ->willReturn($this->paymentQueue);
      $this->element->expects($this->any())
        ->method('getTemporaryPaymentStorage')
        ->willReturn($this->temporaryPaymentStorage);
    }

    /**
     * @covers ::getInfo
     */
    public function testGetInfo() {
      $info = $this->element->getInfo();
      $this->assertInternalType('array', $info);
      $this->assertTrue(is_callable($info['#process'][0]));
    }

    /**
     * @covers ::getTemporaryPaymentStorageKey
     */
    public function testGetTemporaryPaymentStorageKey() {
      $method = new \ReflectionMethod($this->element, 'getTemporaryPaymentStorageKey');
      $method->setAccessible(TRUE);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $key = $method->invoke($this->element, $element, $form_state);
      $this->assertSame($key, $method->invoke($this->element, $element, $form_state));
    }

    /**
     * @covers ::hasTemporaryPayment
     *
     * @depends testGetTemporaryPaymentStorageKey
     */
    public function testHasTemporaryPayment() {
      $has_method = new \ReflectionMethod($this->element, 'hasTemporaryPayment');
      $has_method->setAccessible(TRUE);
      $get_key_method = new \ReflectionMethod($this->element, 'getTemporaryPaymentStorageKey');
      $get_key_method->setAccessible(TRUE);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $key = $get_key_method->invoke($this->element, $element, $form_state);

      // Use a random string instead of a boolean so we can assert the value.
      $has = $this->randomMachineName();

      $this->temporaryPaymentStorage->expects($this->once())
        ->method('has')
        ->with($key)
        ->willReturn($has);

      $this->assertSame($has, $has_method->invoke($this->element, $element, $form_state));
    }

    /**
     * @covers ::getTemporaryPayment
     *
     * @depends testGetTemporaryPaymentStorageKey
     */
    public function testGetTemporaryPayment() {
      $has_method = new \ReflectionMethod($this->element, 'getTemporaryPayment');
      $has_method->setAccessible(TRUE);
      $get_key_method = new \ReflectionMethod($this->element, 'getTemporaryPaymentStorageKey');
      $get_key_method->setAccessible(TRUE);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $key = $get_key_method->invoke($this->element, $element, $form_state);

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $this->temporaryPaymentStorage->expects($this->once())
        ->method('get')
        ->with($key)
        ->willReturn($payment);

      $this->assertSame($payment, $has_method->invoke($this->element, $element, $form_state));
    }

    /**
     * @covers ::SetTemporaryPayment
     *
     * @depends testGetTemporaryPaymentStorageKey
     */
    public function testSetTemporaryPayment() {
      $set_method = new \ReflectionMethod($this->element, 'setTemporaryPayment');
      $set_method->setAccessible(TRUE);
      $get_method = new \ReflectionMethod($this->element, 'getTemporaryPaymentStorageKey');
      $get_method->setAccessible(TRUE);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $key = $get_method->invoke($this->element, $element, $form_state);

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $this->temporaryPaymentStorage->expects($this->once())
        ->method('setWithExpire')
        ->with($key, $payment, \Drupal\payment\Element\PaymentReferenceBase::KEY_VALUE_TTL)
        ->willReturn($payment);

      $set_method->invoke($this->element, $element, $form_state, $payment);
    }

    /**
     * @covers ::valueCallback
     */
    public function testValueCallbackWithDefaultValue() {
      $payment_id = mt_rand();
      $input = $this->randomMachineName();
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $element = array(
        '#default_value' => $payment_id,
      );

      $element_sut = $this->element;
      $this->assertSame($payment_id, $element_sut::valueCallback($element, $input, $form_state));
    }

    /**
     * @covers ::valueCallback
     */
    public function testValueCallbackWithoutDefaultValue() {
      $queue_category_id = $this->randomMachineName();
      $queue_owner_id = $this->randomMachineName();
      $payment_id = mt_rand();

      $element = array(
        '#default_value' => NULL,
        '#queue_category_id' => $queue_category_id,
        '#queue_owner_id' => $queue_owner_id,
        '#type' => $this->randomMachineName(),
      );
      $input = $this->randomMachineName();
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $element_plugin = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
      $element_plugin->expects($this->atLeastOnce())
        ->method('getPaymentQueue')
        ->willReturn($this->paymentQueue);

      // We cannot mock ElementInfoManagerInterface, because it does not extend
      // PluginManagerInterface.
      $element_info_manager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');
      $element_info_manager->expects($this->once())
        ->method('createInstance')
        ->with($element['#type'])
        ->willReturn($element_plugin);

      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $container->expects($this->once())
        ->method('get')
        ->with('plugin.manager.element_info')
        ->willReturn($element_info_manager);

      \Drupal::setContainer($container);

      $this->paymentQueue->expects($this->once())
        ->method('loadPaymentIds')
        ->with($queue_category_id, $queue_owner_id)
        ->willReturn(array($payment_id));

      $element_sut = $this->element;
      $this->assertSame($payment_id, $element_sut::valueCallback($element, $input, $form_state));
    }

    /**
     * @covers ::pay
     *
     * @dataProvider providerTestPay
     */
    public function testPay($is_payment_execution_interruptive, $is_xml_http_request) {
      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition = [];

      $this->element = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->setConstructorArgs(array($configuration, $plugin_id, $this->pluginDefinition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random()))
        ->setMethods(array('getEntityFormDisplay', 'getPaymentMethodSelector', 'getTemporaryPaymentStorageKey', 'setTemporaryPayment'))
        ->getMockForAbstractClass();

      $form = array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              '#id' => $this->randomMachineName(),
              'payment_form' => array(
                'pay' => array(
                  '#array_parents' => array('foo', 'bar', 'container', 'payment_form', 'pay'),
                ),
                'payment_method' => array(
                  '#foo' => $this->randomMachineName(),
                ),
              ),
            ),
          ),
        ),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->atLeastOnce())
        ->method('getTriggeringElement')
        ->willReturn($form['foo']['bar']['container']['payment_form']['pay']);
      $form_state->expects($this->once())
        ->method('setRebuild')
        ->with(TRUE);

      $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
        ->disableOriginalConstructor()
        ->getMock();
      $request->expects($is_payment_execution_interruptive ? $this->atLeastOnce() : $this->never())
        ->method('isXmlHttpRequest')
        ->willReturn($is_xml_http_request);

      $this->requestStack->expects($is_payment_execution_interruptive ? $this->atLeastOnce() : $this->never())
        ->method('getCurrentRequest')
        ->willReturn($request);

      $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
      $payment_method->expects($this->atLeastOnce())
        ->method('isPaymentExecutionInterruptive')
        ->willReturn($is_payment_execution_interruptive);

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment->expects($is_payment_execution_interruptive ? $this->never() : $this->once())
        ->method('execute');
      $payment->expects($is_payment_execution_interruptive ? $this->never() : $this->once())
        ->method('save');
      $payment->expects($this->once())
        ->method('setPaymentMethod')
        ->with($payment_method);

      $payment_method_selector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');
      $payment_method_selector->expects($this->atLeastOnce())
        ->method('getPayment')
        ->willReturn($payment);
      $payment_method_selector->expects($this->atLeastOnce())
        ->method('getPaymentMethod')
        ->willReturn($payment_method);
      $payment_method_selector->expects($this->once())
        ->method('submitConfigurationForm')
        ->with($form['foo']['bar']['container']['payment_form']['payment_method'], $form_state);

      $entity_form_display = $this->getMock('\Drupal\Core\Entity\Display\EntityFormDisplayInterface');
      $entity_form_display->expects($this->once())
        ->method('extractFormValues')
        ->with($payment, $form['foo']['bar']['container']['payment_form'], $form_state);

      $this->element->expects($this->atLeastOnce())
        ->method('getEntityFormDisplay')
        ->willReturn($entity_form_display);
      $this->element->expects($this->atLeastOnce())
        ->method('getPaymentMethodSelector')
        ->willReturn($payment_method_selector);
      $this->element->expects($is_payment_execution_interruptive ? $this->once() : $this->never())
        ->method('setTemporaryPayment')
        ->with($form['foo']['bar'], $form_state, $payment);

      $this->element->pay($form, $form_state);
    }

    /**
     * Provides data to self::testPay().
     */
    public function providerTestPay() {
      return array(
        array(TRUE, TRUE),
        array(TRUE, FALSE),
        array(FALSE, FALSE),
      );
    }

    /**
     * @covers ::ajaxPay
     *
     * @dataProvider providerTestAjaxPay
     */
    public function testAjaxPay($is_payment_execution_interruptive, $number_of_commands) {
      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment->expects($this->atLeastOnce())
        ->method('createDuplicate')
        ->willReturnSelf();

      $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
      $payment_method->expects($this->once())
        ->method('isPaymentExecutionInterruptive')
        ->willReturn($is_payment_execution_interruptive);

      $payment_method_selector_plugin_id = $this->randomMachineName();

      $payment_method_selector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');
      $payment_method_selector->expects($this->atLeastOnce())
        ->method('getPaymentMethod')
        ->willReturn($payment_method);

      $this->paymentMethodSelectorManager->expects($this->atLeastOnce())
      ->method('createInstance')
        ->with($payment_method_selector_plugin_id)
        ->willReturn($payment_method_selector);

      $form = array(
        'foo' => array(
          'bar' => array(
            '#limit_allowed_payment_method_ids' => [],
            '#name' => $this->randomMachineName(),
            '#payment_method_selector_id' => $payment_method_selector_plugin_id,
            '#prototype_payment' => $payment,
            '#required' => TRUE,
            'container' => array(
              '#id' => $this->randomMachineName(),
              'payment_form' => array(
                'pay' => array(
                  '#array_parents' => array('foo', 'bar', 'container', 'payment_form', 'pay'),
                ),
              ),
            ),
          ),
        ),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $map = array(
        array('payment_reference.element.payment_reference.payment_method_selector.' . $form['foo']['bar']['#name'], $payment_method_selector),
      );
      $form_state->expects($this->atLeastOnce())
        ->method('get')
        ->willReturnMap($map);
      $form_state->expects($this->atLeastOnce())
        ->method('getTriggeringElement')
        ->willReturn($form['foo']['bar']['container']['payment_form']['pay']);

      $response = $this->element->ajaxPay($form, $form_state);
      $this->assertInstanceOf('\Drupal\Core\Ajax\AjaxResponse', $response);
      $this->assertCount($number_of_commands, $response->getCommands());
    }

    /**
     * Provides data to self::testAjaxPay().
     */
    public function providerTestAjaxPay() {
      return array(
        array(TRUE, 2),
        array(FALSE, 1),
      );
    }

    /**
     * @covers ::refresh
     */
    public function testRefresh() {
      $form = [];
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRebuild')
        ->with(TRUE);

      $this->element->refresh($form, $form_state);
    }

    /**
     * @covers ::ajaxRefresh
     */
    public function testAjaxRefresh() {
      $form = array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              '#id' => $this->randomMachineName(),
              'refresh' => array(
                '#array_parents' => array('foo', 'bar', 'container', 'refresh'),
              ),
            ),
          ),
        ),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('getTriggeringElement')
        ->willReturn($form['foo']['bar']['container']['refresh']);

      $response = $this->element->ajaxRefresh($form, $form_state);
      $this->assertInstanceOf('\Drupal\Core\Ajax\AjaxResponse', $response);
    }

    /**
     * @covers ::disableChildren
     */
    public function testDisableChildren() {
      $element = array(
        'foo' => array(
          '#foo' => $this->randomMachineName(),
          'bar' => array(
            '#bar' => $this->randomMachineName(),
          ),
        ),
      );

      $expected_element = $element;
      $expected_element['foo']['#disabled'] = TRUE;
      $expected_element['foo']['bar']['#disabled'] = TRUE;

      $method = new \ReflectionMethod($this->element, 'disableChildren');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->element, array(&$element));
      $this->assertSame($expected_element, $element);
    }

    /**
     * @covers ::getPaymentMethodSelector
     *
     * @dataProvider providerGetPaymentMethodSelector
     */
    public function testGetPaymentMethodSelector($limit_allowed_payment_method_ids) {
      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment->expects($this->once())
        ->method('createDuplicate')
        ->willReturnSelf();

      $payment_method_selector_plugin_id = $this->randomMachineName();
      $required = $this->randomMachineName();

      $element = array(
        '#limit_allowed_payment_method_ids' => $limit_allowed_payment_method_ids,
        '#name' => $this->randomMachineName(),
        '#payment_method_selector_id' => $payment_method_selector_plugin_id,
        '#prototype_payment' => $payment,
        '#required' => $required,
      );
      $form_state = new FormState();

      $payment_method_selector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');
      $payment_method_selector->expects(is_null($limit_allowed_payment_method_ids) ? $this->never() : $this->once())
        ->method('setAllowedPaymentMethods')
        ->with($limit_allowed_payment_method_ids);
      $payment_method_selector->expects($this->once())
        ->method('setPayment')
        ->with($payment);
      $payment_method_selector->expects($this->once())
        ->method('setRequired')
        ->with($required);

      $this->paymentMethodSelectorManager->expects($this->once())
        ->method('createInstance')
        ->with($payment_method_selector_plugin_id)
        ->willReturn($payment_method_selector);

      $method = new \ReflectionMethod($this->element, 'getPaymentMethodSelector');
      $method->setAccessible(TRUE);

      $retrieved_payment_method_selector = $method->invoke($this->element, $element, $form_state);
      $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface', $retrieved_payment_method_selector);
      $this->assertSame($retrieved_payment_method_selector, $method->invoke($this->element, $element, $form_state));
    }

    /**
     * Provides data to self::testProviderGetPaymentMethodSelector().
     */
    public function providerGetPaymentMethodSelector() {
      return array(
        array(NULL),
        array([]),
        array(array($this->randomMachineName(), $this->randomMachineName())),
      );
    }

    /**
     * @covers ::buildCompletePaymentLink
     */
    public function testBuildCompletePaymentLinkWithoutPaymentMethod() {
      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition = [];

      $element = array(
        '#foo' => $this->randomMachineName(),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $payment_method_selector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');

      $this->element = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->setConstructorArgs(array($configuration, $plugin_id, $this->pluginDefinition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random()))
        ->setMethods(array('getPaymentMethodSelector', 'getTemporaryPaymentStorageKey'))
        ->getMockForAbstractClass();
      $this->element->expects($this->atLeastOnce())
        ->method('getPaymentMethodSelector')
        ->with($element, $form_state)
        ->willReturn($payment_method_selector);
      $this->element->expects($this->never())
        ->method('getTemporaryPaymentStorageKey')
        ->with($element, $form_state);


      $method = new \ReflectionMethod($this->element, 'buildCompletePaymentLink');
      $method->setAccessible(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertSame([], $build);
    }

    /**
     * @covers ::buildCompletePaymentLink
     */
    public function testBuildCompletePaymentLinkWithPaymentMethod() {
      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $this->pluginDefinition = [];

      $element = array(
        '#foo' => $this->randomMachineName(),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

      $payment_method_selector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');
      $payment_method_selector->expects($this->atLeastOnce())
        ->method('getPaymentMethod')
        ->willReturn($payment_method);

      $this->element = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->setConstructorArgs(array($configuration, $plugin_id, $this->pluginDefinition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random()))
        ->setMethods(array('getPaymentMethodSelector', 'getTemporaryPaymentStorageKey'))
        ->getMockForAbstractClass();
      $this->element->expects($this->atLeastOnce())
        ->method('getPaymentMethodSelector')
        ->with($element, $form_state)
        ->willReturn($payment_method_selector);
      $this->element->expects($this->atLeastOnce())
        ->method('getTemporaryPaymentStorageKey')
        ->with($element, $form_state);


      $method = new \ReflectionMethod($this->element, 'buildCompletePaymentLink');
      $method->setAccessible(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertInternalType('array', $build);
      $this->assertSame('link', $build['link']['#type']);
    }

    /**
     * @covers ::buildPaymentView
     */
    public function testBuildPaymentViewWithoutPaymentWithDefaultValue() {
      $element = array(
        '#default_value' => mt_rand(),
        '#available_payment_id' => mt_rand(),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $this->paymentStorage->expects($this->once())
        ->method('load')
        ->with($element['#default_value']);

      $method = new \ReflectionMethod($this->element, 'buildPaymentView');
      $method->setAccessible(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertSame([], $build);
    }

    /**
     * @covers ::buildPaymentView
     */
    public function testBuildPaymentViewWithoutPaymentWithOutDefaultValue() {
      $element = array(
        '#default_value' => NULL,
        '#available_payment_id' => mt_rand(),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $this->paymentStorage->expects($this->once())
        ->method('load')
        ->with($element['#available_payment_id']);

      $method = new \ReflectionMethod($this->element, 'buildPaymentView');
      $method->setAccessible(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertSame([], $build);
    }

    /**
     * @covers ::buildPaymentView
     *
     * @dataProvider providerTestBuildPaymentViewWithPayment
     */
    public function testBuildPaymentViewWithPayment($view_access) {
      $element = array(
        '#default_value' => mt_rand(),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $currency = $this->getMockBuilder('\Drupal\currency\Entity\Currency')
        ->disableOriginalConstructor()
        ->getMock();

      $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment->expects($this->atLeastOnce())
        ->method('access')
        ->willReturn($view_access);
      $payment->expects($this->atLeastOnce())
        ->method('getCurrency')
        ->willReturn($currency);
      $payment->expects($this->atLeastOnce())
        ->method('getPaymentStatus')
        ->willReturn($payment_status);
      $payment->expects($view_access ? $this->once() : $this->never())
        ->method('url');

      $this->paymentStorage->expects($this->once())
        ->method('load')
        ->with($element['#default_value'])
        ->willReturn($payment);

      $method = new \ReflectionMethod($this->element, 'buildPaymentView');
      $method->setAccessible(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertInternalType('array', $build);
    }

    /**
     * Provides data to self::testBuildPaymentViewWithPayment().
     */
    public function providerTestBuildPaymentViewWithPayment() {
      return array(
        array(TRUE),
        array(FALSE),
      );
    }

    /**
     * @covers ::buildRefreshButton
     */
    public function testBuildRefreshButton() {
      $element = array(
        '#default_value' => mt_rand(),
        'container' => array(
          '#id' => $this->randomMachineName(),
        ),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $method = new \ReflectionMethod($this->element, 'buildRefreshButton');
      $method->setAccessible(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertInternalType('array', $build);
      $this->assertInstanceOf('\Closure', $build['#ajax']['callback']);
      $this->assertSame($build['#submit'][0][0], $this->pluginDefinition['class']);
    }

    /**
     * @covers ::buildPaymentForm
     */
    public function testBuildPaymentForm() {
      $element = array(
        '#parents' => array($this->randomMachineName()),
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $method = new \ReflectionMethod($this->element, 'buildPaymentForm');
      $method->setAccessible(TRUE);

      $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $payment_method_selector = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorInterface');
      $payment_method_selector->expects($this->atLeastOnce())
        ->method('buildConfigurationForm')
        ->with([], $form_state)
        ->willReturn([]);
      $payment_method_selector->expects($this->atLeastOnce())
        ->method('getPayment')
        ->willReturn($payment);

      $configuration = [];
      $plugin_id = $this->randomMachineName();

      $entity_form_display = $this->getMock('\Drupal\Core\Entity\Display\EntityFormDisplayInterface');
      $entity_form_display->expects($this->once())
        ->method('buildForm')
        ->with($payment, $this->isType('array'), $form_state);

      $this->element = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->setConstructorArgs(array($configuration, $plugin_id, $this->pluginDefinition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random()))
        ->setMethods(array('getEntityFormDisplay', 'getPaymentMethodSelector', 'hasTemporaryPayment'))
        ->getMockForAbstractClass();
      $this->element->expects($this->atLeastOnce())
        ->method('getEntityFormDisplay')
        ->willReturn($entity_form_display);
      $this->element->expects($this->atLeastOnce())
        ->method('getPaymentMethodSelector')
        ->with($element, $form_state)
        ->willReturn($payment_method_selector);
      $this->element->expects($this->atLeastOnce())
        ->method('hasTemporaryPayment')
        ->with($element, $form_state)
        ->willReturn(TRUE);

      $build = $method->invoke($this->element, $element, $form_state);
      $this->assertInternalType('array', $build);
      $this->assertInstanceOf('\Closure', $build['pay_button']['#ajax']['callback']);
      $this->assertInstanceOf('\Closure', $build['pay_button']['#submit'][0]);
      $this->assertTrue(is_callable($build['pay_button']['#process'][0]));
      $this->assertTrue(is_callable($build['pay_link']['#process'][0]));
    }

    /**
     * @covers ::processMaxWeight
     */
    public function testProcessMaxWeight() {
      $sibling_weight_1 = mt_rand();
      $sibling_weight_2 = mt_rand();
      $form = array(
        'foo' => array(
          'bar' => array(
            'sibling_1' => array(
              '#weight' => $sibling_weight_1,
            ),
            'sibling_2' => array(
              '#weight' => $sibling_weight_2,
            ),
            'subject' => array(
              '#array_parents' => array('foo', 'bar', 'subject'),
            ),
          ),
        ),
      );
      $element = $form['foo']['bar']['subject'];
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

      $element_plugin = $this->element;
      $element = $element_plugin::processMaxWeight($element, $form_state, $form);
      $this->assertGreaterThan(max($sibling_weight_1, $sibling_weight_2), $element['#weight']);
    }

    /**
     * @covers ::process
     *
     * @depends testGetInfo
     */
    public function testProcess() {
      $name = $this->randomMachineName();
      $prototype_payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment_method_selector_id = $this->randomMachineName();
      $queue_category_id = $this->randomMachineName();
      $queue_owner_id = mt_rand();

      $element = array(
        '#default_value' => NULL,
        '#limit_allowed_payment_method_ids' => NULL,
        '#name' => $name,
        '#payment_method_selector_id' => $payment_method_selector_id,
        '#prototype_payment' => $prototype_payment,
        '#queue_category_id' => $queue_category_id,
        '#queue_owner_id' => $queue_owner_id,
      );
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form = [];

      $configuration = [];
      $plugin_id = $this->randomMachineName();

      $payment_form = array(
        '#foo' => $this->randomMachineName(),
      );

      $payment_view = array(
        '#foo' => $this->randomMachineName(),
      );

      $refresh_button = array(
        '#foo' => $this->randomMachineName(),
      );

      $this->element = $this->getMockBuilder('\Drupal\payment\Element\PaymentReferenceBase')
        ->setConstructorArgs(array($configuration, $plugin_id, $this->pluginDefinition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random()))
        ->setMethods(array('buildPaymentForm', 'buildPaymentView', 'buildRefreshButton'))
        ->getMockForAbstractClass();
      $this->element->expects($this->atLeastOnce())
        ->method('buildPaymentForm')
        ->with($this->isType('array'), $form_state)
        ->willReturn($payment_form);
      $this->element->expects($this->atLeastOnce())
        ->method('buildPaymentView')
        ->with($this->isType('array'), $form_state)
        ->willReturn($payment_view);
      $this->element->expects($this->atLeastOnce())
        ->method('buildRefreshButton')
        ->with($this->isType('array'), $form_state)
        ->willReturn($refresh_button);
      $this->element->expects($this->atLeastOnce())
        ->method('getPaymentQueue')
        ->willReturn($this->paymentQueue);

      $build = $this->element->process($element, $form_state, $form);
      $this->assertTrue(is_callable($build['#element_validate'][0]));
      $this->assertTrue($build['#tree']);
      unset($build['container']['payment_form']['#access']);
      $this->assertSame($payment_form, $build['container']['payment_form']);
      unset($build['container']['payment_view']['#access']);
      $this->assertSame($payment_view, $build['container']['payment_view']);
    }

    /**
     * @covers ::process
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider providerTestProcess
     */
    public function testProcessWithInvalidElementConfiguration(array $element) {
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form = [];

      $this->element->process($element, $form_state, $form);
    }

    /**
     * Provides data to self::testProcess().
     */
    public function providerTestProcess() {
      $name = $this->randomMachineName();
      $prototype_payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();
      $payment_method_selector_id = $this->randomMachineName();
      $queue_category_id = $this->randomMachineName();
      $queue_owner_id = mt_rand();

      $element = array(
        '#default_value' => NULL,
        '#limit_allowed_payment_method_ids' => NULL,
        '#name' => $name,
        '#payment_method_selector_id' => $payment_method_selector_id,
        '#prototype_payment' => $prototype_payment,
        '#queue_category_id' => $queue_category_id,
        '#queue_owner_id' => $queue_owner_id,
      );

      return array(
        array(array_merge($element, array(
          '#default_value' => $this->randomMachineName(),
        ))),
        array(array_merge($element, array(
          '#limit_allowed_payment_method_ids' => $this->randomMachineName(),
        ))),
        array(array_merge($element, array(
          '#queue_category_id' => mt_rand(),
        ))),
        array(array_merge($element, array(
          '#queue_owner_id' => $this->randomMachineName(),
        ))),
        array(array_merge($element, array(
          '#payment_method_selector_id' => mt_rand(),
        ))),
        array(array_merge($element, array(
          '#prototype_payment' => $this->randomMachineName(),
        ))),
      );
    }

  }

}

namespace {

  if (!function_exists('drupal_process_attached')) {
    function drupal_process_attached() {
    }
  }
  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {
    }
  }

}
