<?php

/**
 * @file
 * Contains
 * \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodSelector {

use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect
 */
class PaymentSelectUnitTest extends UnitTestCase {

  /**
   * The current user used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The payment method manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment method selector plugin under test.
   *
   * @var \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect
   */
  protected $paymentMethodSelector;

  /**
   * The ID of the payment method selector plugin under test.
   *
   * @var string
   */
  protected $paymentMethodSelectorPluginId;

  /**
   * The string translation service.
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
      'name' => '\Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->paymentMethodSelectorPluginId = $this->randomName();
    $this->paymentMethodSelector = new PaymentSelectUnitTestPaymentSelect(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $plugin = PaymentSelect::create($container, array(), $this->paymentMethodSelectorPluginId, array());
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect', $plugin);
  }

  /**
   * @covers ::buildPaymentMethodForm
   */
  public function testBuildPaymentMethodForm() {
    $plugin_id = $this->randomName();

    $payment_method_form = array(
      '#type' => $this->randomName(),
    );

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('buildConfigurationForm')
      ->with(array(), $this->isType('array'))
      ->will($this->returnValue($payment_method_form));

    $expected_build = array(
      '#id' => NULL,
      '#type' => 'container',
    );

    $method = new \ReflectionMethod($this->paymentMethodSelector, 'buildPaymentMethodForm');
    $method->setAccessible(TRUE);

    $form_state = array();

    $build = $method->invoke($this->paymentMethodSelector, array(&$form_state));
    $this->assertEquals($expected_build, $build);

    $this->paymentMethodSelector->setPaymentMethod($payment_method);
    $build = $method->invoke($this->paymentMethodSelector, array(&$form_state));
    $expected_build += $payment_method_form;
    $this->assertEquals($expected_build, $build);
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithoutAvailablePaymentMethods() {
    $form = array();
    $form_state = array();

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodSelector->setPayment($payment);

    $this->paymentMethodManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue(array()));

    $build = $this->paymentMethodSelector->buildConfigurationForm($form, $form_state);

    $expected_build = array(
      'container' => array(
        '#available_payment_methods' => array(),
        // The element does not actually have input, but we need the #name
        // property to be populated by form API.
        '#input' => TRUE,
        '#process' => array(array($this->paymentMethodSelector, 'buildNoAvailablePaymentMethods')),
        '#tree' => TRUE,
        '#type' => 'container',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithOneAvailablePaymentMethod() {
    $form = array();
    $form_state = array();

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    /** @var \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect')
      ->setMethods(array('getAvailablePaymentMethods'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMock();
    $payment_method_selector->expects($this->once())
      ->method('getAvailablePaymentMethods')
      ->will($this->returnValue(array($payment_method)));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodSelector->setPayment($payment);

    $this->paymentMethodManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue(array()));

    $build = $payment_method_selector->buildConfigurationForm($form, $form_state);

    $expected_build = array(
      'container' => array(
        '#available_payment_methods' => array($payment_method),
        // The element does not actually have input, but we need the #name
        // property to be populated by form API.
        '#input' => TRUE,
        '#process' => array(array($payment_method_selector, 'buildOneAvailablePaymentMethod')),
        '#tree' => TRUE,
        '#type' => 'container',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithMultipleAvailablePaymentMethods() {
    $form = array();
    $form_state = array();

    $payment_method_a = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method_b = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    /** @var \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect')
      ->setMethods(array('getAvailablePaymentMethods'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMock();
    $payment_method_selector->expects($this->once())
      ->method('getAvailablePaymentMethods')
      ->will($this->returnValue(array($payment_method_a, $payment_method_b)));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodSelector->setPayment($payment);

    $this->paymentMethodManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue(array()));

    $build = $payment_method_selector->buildConfigurationForm($form, $form_state);

    $expected_build = array(
      'container' => array(
        '#available_payment_methods' => array($payment_method_a, $payment_method_b),
        // The element does not actually have input, but we need the #name
        // property to be populated by form API.
        '#input' => TRUE,
        '#process' => array(array($payment_method_selector, 'buildMultipleAvailablePaymentMethods')),
        '#tree' => TRUE,
        '#type' => 'container',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = array(
      'container' => array(
        'payment_method_form' => array(
          $this->randomName() => array(),
        ),
      ),
    );
    $form_state = array();

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('submitConfigurationForm')
      ->with($form['container']['payment_method_form'], $form_state);

    $this->paymentMethodSelector->submitConfigurationForm($form, $form_state);
    $this->paymentMethodSelector->setPaymentMethod($payment_method);
    $this->paymentMethodSelector->submitConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::validateConfigurationForm
   * @covers ::getPaymentMethod
   */
  public function testValidateConfigurationForm() {
    $payment_method_id_a = $this->randomName();
    $payment_method_id_b = $this->randomName();

    $form = array(
      'container' => array(
        '#parents' => array('foo', 'bar', 'container'),
        'payment_method_form' => array(
          $this->randomName() => array(),
        ),
      ),
    );
    $form_state = array(
      'values' => array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'payment_method_id' => $payment_method_id_a,
              ),
            ),
          ),
        ),
      ),
    );

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodSelector->setPayment($payment);

    $payment_method_a = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method_a->expects($this->once())
      ->method('validateConfigurationForm')
      ->with($form['container']['payment_method_form'], $form_state);
    $payment_method_a->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($payment_method_id_a));
    $payment_method_a->expects($this->once())
      ->method('setPayment')
      ->with($payment);
    $payment_method_b = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method_b->expects($this->never())
      ->method('validateConfigurationForm');
    $payment_method_a->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($payment_method_id_b));

    $map = array(
      array($payment_method_id_a, array(), $payment_method_a),
      array($payment_method_id_b, array(), $payment_method_b),
    );
    $this->paymentMethodManager->expects($this->exactly(2))
      ->method('createInstance')
      ->will($this->returnValueMap($map));

    // The payment method is set for the first time. The payment method form
    // must not be validated, as there is no input for it yet,
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_a, $this->paymentMethodSelector->getPaymentMethod());
    $this->assertTrue($form_state['rebuild']);
    unset($form_state['rebuild']);

    // The form is validated, but the payment method remains unchanged, and as
    // such should validate its own form as wel.
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_a, $this->paymentMethodSelector->getPaymentMethod());
    $this->assertArrayNotHasKey('rebuild', $form_state);
    unset($form_state['rebuild']);

    // The payment method is changed. The payment method form must not be
    // validated, as there is no input for it yet,
    $form_state['values']['foo']['bar']['container']['select']['payment_method_id'] = $payment_method_id_b;
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_b, $this->paymentMethodSelector->getPaymentMethod());
    $this->assertTrue($form_state['rebuild']);
    unset($form_state['rebuild']);

    // Change the payment method ID back to the original. No new plugin may be
    // instantiated, nor must the payment method form be validated.
    $form_state['values']['foo']['bar']['container']['select']['payment_method_id'] = $payment_method_id_a;
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_a, $this->paymentMethodSelector->getPaymentMethod());
    $this->assertTrue($form_state['rebuild']);
    unset($form_state['rebuild']);
  }

  /**
   * @covers ::ajaxSubmitConfigurationForm
   */
  public function testAjaxSubmitConfigurationForm() {
    $form = array(
      'foo' => array(
        'bar' => array(
          'payment_method_form' => array(
            $this->randomName() => array(),
          ),
        ),
      ),
    );
    $form_state = array(
      'triggering_element' => array(
        '#array_parents' => array('foo', 'bar', 'baz', 'qux'),
      ),
    );

    $build = $this->paymentMethodSelector->ajaxSubmitConfigurationForm($form, $form_state);
    $this->assertSame($form['foo']['bar']['payment_method_form'], $build);
  }

  /**
   * @covers ::getElementId
   */
  public function testGetElementId() {
    $method = new \ReflectionMethod($this->paymentMethodSelector, 'getElementId');
    $method->setAccessible(TRUE);
    $form_state = array();

    $element_id = $method->invokeArgs($this->paymentMethodSelector, array(&$form_state));
    $this->assertInternalType('integer', strlen($element_id));
    $this->assertSame($element_id, $method->invokeArgs($this->paymentMethodSelector, array(&$form_state)));
  }

  /**
   * @covers ::buildSelector
   */
  public function testBuildSelector() {
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $method = new \ReflectionMethod($this->paymentMethodSelector, 'buildSelector');
    $method->setAccessible(TRUE);
    $get_element_id_method = new \ReflectionMethod($this->paymentMethodSelector, 'getElementId');
    $get_element_id_method->setAccessible(TRUE);

    $payment_method_id = $this->randomName();
    $payment_method_label = $this->randomName();
    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($payment_method_id));
    $payment_method->expects($this->any())
      ->method('getPluginLabel')
      ->will($this->returnValue($payment_method_label));

    $this->paymentMethodSelector->setPaymentMethod($payment_method);

    $element = array(
      '#name' => $this->randomName(),
      '#parents' => array('foo', 'bar'),
    );
    $form_state = array();
    $available_payment_methods = array($payment_method);

    $expected_build = array(
      'payment_method_id' => array(
        '#ajax' => array(
          'effect' => 'fade',
          'event' => 'change',
          'trigger_as' => array(
            'name' => $element['#name'] . '[select][change]',
          ),
          'wrapper' => $get_element_id_method->invokeArgs($this->paymentMethodSelector, array(&$form_state)),
        ),
        '#default_value' => $payment_method_id,
        '#empty_value' => 'select',
        '#options' => array(
          $payment_method_id => $payment_method_label,
        ) ,
        '#required' => FALSE,
        '#title' => 'Payment method',
        '#type' => 'select',
      ),
      'change' => array(
        '#ajax' => array(
          'callback' => array($this->paymentMethodSelector, 'ajaxSubmitConfigurationForm'),
        ),
        '#attributes' => array(
          'class' => array('js-hide')
        ),
        '#limit_validation_errors' => array(array('foo', 'bar', 'select', 'payment_method_id')),
        '#name' => $element['#name'] . '[select][change]',
        '#submit' => array(array($this->paymentMethodSelector, 'rebuildForm')),
        '#type' => 'submit',
        '#value' => 'Choose payment method',
      ),
    );
    $this->assertEquals($expected_build, $method->invokeArgs($this->paymentMethodSelector, array($element, &$form_state, $available_payment_methods)));
  }

  /**
   * @covers ::rebuildForm
   */
  public function testRebuildForm() {
    $form = array();
    $form_state = array();

    $this->paymentMethodSelector->rebuildForm($form, $form_state);
    $this->assertArrayHasKey('rebuild', $form_state);
    $this->assertTrue($form_state['rebuild']);
  }

  /**
   * @covers ::buildNoAvailablePaymentMethods
   */
  public function testBuildNoAvailablePaymentMethods() {
    $element = array();
    $form_state = array();
    $form = array();

    $expected_build = $element + array(
      'select' => array(
        '#tree' => TRUE,
        'payment_method_id' => array(
          '#type' => 'value',
          '#value' => NULL,
        ),
        'message' => array(
          '#markup' => 'There are no available payment methods.',
        ),
      ),
    );
    $this->assertEquals($expected_build, $this->paymentMethodSelector->buildNoAvailablePaymentMethods($element, $form_state, $form));
  }

  /**
   * @covers ::buildOneAvailablePaymentMethod
   */
  public function testBuildOneAvailablePaymentMethod() {
    $plugin_id = $this->randomName();

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id));

    $element = array(
      '#available_payment_methods' => array($payment_method),
    );
    $form_state = array(
      'foo' => $this->randomName(),
    );
    $form = array();

    $payment_method_form = array(
      '#type' => $this->randomName(),
    );

    /** @var \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect')
      ->setMethods(array('buildPaymentMethodForm'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMock();
    $payment_method_selector->expects($this->once())
      ->method('buildPaymentMethodForm')
      ->with($form_state)
      ->will($this->returnValue($payment_method_form));
    $payment_method_selector->setPaymentMethod($payment_method);

    $expected_build = array(
      '#available_payment_methods' => array($payment_method),
      'select' => array(
        'payment_method_id' => array(
          '#type' => 'value',
          '#value' => $plugin_id,
        ),
      ),
      'payment_method_form' => $payment_method_form,
    );
    $this->assertSame($expected_build, $payment_method_selector->buildOneAvailablePaymentMethod($element, $form_state, $form));
  }

  /**
   * @covers ::buildMultipleAvailablePaymentMethods
   */
  public function testBuildMultipleAvailablePaymentMethods() {
    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $element = array(
      '#available_payment_methods' => array($payment_method),
    );
    $form_state = array(
      'foo' => $this->randomName(),
    );
    $form = array();

    $payment_method_form = array(
      '#type' => $this->randomName(),
    );

    $selector = array(
      '#type' => $this->randomName(),
    );

    /** @var \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTestPaymentSelect')
      ->setMethods(array('buildPaymentMethodForm', 'buildSelector'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMock();
    $payment_method_selector->expects($this->once())
      ->method('buildPaymentMethodForm')
      ->with($form_state)
      ->will($this->returnValue($payment_method_form));
    $payment_method_selector->expects($this->once())
      ->method('buildSelector')
      ->with($element, $form_state, array($payment_method))
      ->will($this->returnValue($selector));
    $payment_method_selector->setPaymentMethod($payment_method);

    $expected_build = array(
      '#available_payment_methods' => array($payment_method),
      'select' => $selector,
        'payment_method_form' => $payment_method_form,
    );
    $this->assertEquals($expected_build, $payment_method_selector->buildMultipleAvailablePaymentMethods($element, $form_state, $form));
  }

}

/**
 * Adds testing methods to the class under test.
 */
class PaymentSelectUnitTestPaymentSelect extends PaymentSelect {

  /**
   * Sets the selected payment method.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   *
   * @return $this;
   */
  public function setPaymentMethod(PaymentMethodInterface $payment_method) {
    $this->selectedPaymentMethod = $payment_method;

    return $this;
  }

}

}

namespace {

  if (!function_exists('drupal_html_id')) {
    function drupal_html_id() {}
  }

}
