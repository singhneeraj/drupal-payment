<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment\Unit\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\MethodSelector;

use Drupal\Component\Utility\Html;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase
 *
 * @group Payment
 */
class AdvancedPaymentMethodSelectorBaseUnitTest extends UnitTestCase {

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
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase|\PHPUnit_Framework_MockObject_MockObject
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

    $this->paymentMethodSelectorPluginId = $this->randomMachineName();
    $this->paymentMethodSelector = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase')
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMockForAbstractClass();
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

    /** @var \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase $class */
    $class = get_class($this->paymentMethodSelector);
    $plugin = $class::create($container, array(), $this->paymentMethodSelectorPluginId, array());
    $this->assertInstanceOf('\Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase', $plugin);
  }

  /**
   * @covers ::buildPaymentMethodForm
   */
  public function testBuildPaymentMethodForm() {
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $payment_method_form = array(
      '#foo' => $this->randomMachineName(),
    );

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('buildConfigurationForm')
      ->with(array(), $form_state)
      ->will($this->returnValue($payment_method_form));


    $method = new \ReflectionMethod($this->paymentMethodSelector, 'buildPaymentMethodForm');
    $method->setAccessible(TRUE);

    $build = $method->invoke($this->paymentMethodSelector, $form_state);
    $this->assertSame('container', $build['#type']);

    $this->paymentMethodSelector->setPaymentMethod($payment_method);
    $build = $method->invoke($this->paymentMethodSelector, $form_state);
    $this->assertSame('container', $build['#type']);
    $this->assertSame($payment_method_form['#foo'], $build['#foo']);
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationFormWithoutAvailablePaymentMethods() {
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

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
        '#attributes' => array(
          'class' => array('payment-method-selector-' . Html::getId($this->paymentMethodSelectorPluginId)),
        ),
        '#available_payment_methods' => array(),
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
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    /** @var \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase')
      ->setMethods(array('getAvailablePaymentMethods'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMockForAbstractClass();
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
        '#attributes' => array(
          'class' => array('payment-method-selector-' . Html::getId($this->paymentMethodSelectorPluginId)),
        ),
        '#available_payment_methods' => array($payment_method),
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
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $payment_method_a = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method_b = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    /** @var \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase')
      ->setMethods(array('getAvailablePaymentMethods'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMockForAbstractClass();
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
        '#attributes' => array(
          'class' => array('payment-method-selector-' . Html::getId($this->paymentMethodSelectorPluginId)),
        ),
        '#available_payment_methods' => array($payment_method_a, $payment_method_b),
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
          $this->randomMachineName() => array(),
        ),
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

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
   */
  public function testValidateConfigurationForm() {
    $payment_method_id_a = $this->randomMachineName();
    $payment_method_id_b = $this->randomMachineName();

    $form = array(
      'container' => array(
        '#parents' => array('foo', 'bar', 'container'),
        'payment_method_form' => array(
          $this->randomMachineName() => array(),
        ),
      ),
    );

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentMethodSelector->setPayment($payment);

    $payment_method_a = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method_a->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($payment_method_id_a));
    $payment_method_b = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method_b->expects($this->never())
      ->method('validateConfigurationForm');
    $payment_method_b->expects($this->any())
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
    // must not be validated, as there is no input for it yet.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'payment_method_id' => $payment_method_id_a,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->once())
      ->method('setRebuild');
    $payment_method_a->expects($this->once())
      ->method('setPayment')
      ->with($payment);
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_a, $this->paymentMethodSelector->getPaymentMethod());

    // The form is validated, but the payment method remains unchanged, and as
    // such should validate its own form as well.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'payment_method_id' => $payment_method_id_a,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->never())
      ->method('setRebuild');
    $payment_method_a->expects($this->once())
      ->method('validateConfigurationForm')
      ->with($form['container']['payment_method_form'], $form_state);
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_a, $this->paymentMethodSelector->getPaymentMethod());

    // The payment method is changed. The payment method form must not be
    // validated, as there is no input for it yet.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'payment_method_id' => $payment_method_id_b,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->once())
      ->method('setRebuild');
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_b, $this->paymentMethodSelector->getPaymentMethod());

    // Change the payment method ID back to the original. No new plugin may be
    // instantiated, nor must the payment method form be validated.
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getValues')
      ->willReturn(array(
        'foo' => array(
          'bar' => array(
            'container' => array(
              'select' => array(
                'container' => array(
                  'payment_method_id' => $payment_method_id_a,
                ),
              ),
            ),
          ),
        ),
      ));
    $form_state->expects($this->once())
      ->method('setRebuild');
    $this->paymentMethodSelector->validateConfigurationForm($form, $form_state);
    $this->assertSame($payment_method_a, $this->paymentMethodSelector->getPaymentMethod());
  }

  /**
   * @covers ::ajaxRebuildForm
   */
  public function testAjaxRebuildForm() {
    $form = array(
      'foo' => array(
        'bar' => array(
          'container' => array(
            'select' => array(
              'container' => array(
                'change' => array(
                  '#array_parents' => array('foo', 'bar', 'container', 'select', 'container', 'change'),
                ),
              ),
            ),
            'payment_method_form' => array(
              $this->randomMachineName() => array(),
            ),
          ),
        ),
      ),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->atLeastOnce())
      ->method('getTriggeringElement')
      ->willReturn($form['foo']['bar']['container']['select']['container']['change']);

    $build = $this->paymentMethodSelector->ajaxRebuildForm($form, $form_state);
    $this->assertSame($form['foo']['bar']['container']['payment_method_form'], $build);
  }

  /**
   * @covers ::getElementId
   */
  public function testGetElementId() {
    $method = new \ReflectionMethod($this->paymentMethodSelector, 'getElementId');
    $method->setAccessible(TRUE);
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $element_id = $method->invokeArgs($this->paymentMethodSelector, array($form_state));
    $this->assertInternalType('integer', strlen($element_id));
    $this->assertSame($element_id, $method->invokeArgs($this->paymentMethodSelector, array($form_state)));
  }

  /**
   * @covers ::rebuildForm
   */
  public function testRebuildForm() {
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form_state->expects($this->once())
      ->method('setRebuild')
      ->with(TRUE);

    $this->paymentMethodSelector->rebuildForm($form, $form_state);
  }

  /**
   * @covers ::buildNoAvailablePaymentMethods
   */
  public function testBuildNoAvailablePaymentMethods() {
    $element = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = array();

    $expected_build = $element + array(
      'select' => array(
        'message' => array(
          '#markup' => 'There are no available payment methods.',
        ),
        'container' => array(
          '#type' => 'container',
          'payment_method_id' => array(
            '#type' => 'value',
            '#value' => NULL,
          ),
        ),
      ),
    );
    $this->assertEquals($expected_build, $this->paymentMethodSelector->buildNoAvailablePaymentMethods($element, $form_state, $form));
  }

  /**
   * @covers ::buildOneAvailablePaymentMethod
   */
  public function testBuildOneAvailablePaymentMethod() {
    $plugin_id = $this->randomMachineName();

    $payment_method_form = array(
      '#type' => $this->randomMachineName(),
    );

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->atLeastOnce())
      ->method('getPluginId')
      ->will($this->returnValue($plugin_id));
    $payment_method->expects($this->once())
      ->method('buildConfigurationForm')
      ->will($this->returnValue($payment_method_form));

    $element = array(
      '#available_payment_methods' => array($payment_method),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = array();


    $expected_build = array(
      '#available_payment_methods' => array($payment_method),
      'select' => array(
        'container' => array(
          '#type' => 'container',
          'payment_method_id' => array(
            '#type' => 'value',
            '#value' => $plugin_id,
          ),
        ),
      ),
      'payment_method_form' => array(
        '#attributes' => array(
          'class' => array('payment-method-selector-' . Html::getId($this->paymentMethodSelectorPluginId) . '-payment-method-form'),
        ),
        '#type' => 'container',
      ) + $payment_method_form,
    );
    $build = $this->paymentMethodSelector->buildOneAvailablePaymentMethod($element, $form_state, $form);
    unset($build['payment_method_form']['#id']);
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::buildMultipleAvailablePaymentMethods
   */
  public function testBuildMultipleAvailablePaymentMethods() {
    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $element = array(
      '#available_payment_methods' => array($payment_method),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form = array();

    $payment_method_form = array(
      '#type' => $this->randomMachineName(),
    );

    $selector = array(
      '#type' => $this->randomMachineName(),
    );

    /** @var \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase|\PHPUnit_Framework_MockObject_MockObject $payment_method_selector */
    $payment_method_selector = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase')
      ->setMethods(array('buildPaymentMethodForm', 'buildSelector'))
      ->setConstructorArgs(array(array(), $this->paymentMethodSelectorPluginId, array(), $this->currentUser, $this->paymentMethodManager, $this->stringTranslation))
      ->getMockForAbstractClass();
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

  /**
   * @covers ::setPaymentMethod
   * @covers ::getPaymentMethod
   */
  public function testGetPaymentMethod() {
    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $this->assertSame($this->paymentMethodSelector, $this->paymentMethodSelector->setPaymentMethod($payment_method));
    $this->assertSame($payment_method, $this->paymentMethodSelector->getPaymentMethod());
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

    $payment_method_id = $this->randomMachineName();
    $payment_method_label = $this->randomMachineName();
    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $payment_method->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($payment_method_id));
    $payment_method->expects($this->any())
      ->method('getPluginLabel')
      ->will($this->returnValue($payment_method_label));

    $this->paymentMethodSelector->setPaymentMethod($payment_method);

    $element = array(
      '#parents' => array('foo', 'bar'),
    );
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $available_payment_methods = array($payment_method);

    $expected_build_change = array(
      '#ajax' => array(
        'callback' => array('Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase', 'ajaxRebuildForm'),
      ),
      '#attributes' => array(
        'class' => array('js-hide')
      ),
      '#limit_validation_errors' => array(array('foo', 'bar', 'select', 'payment_method_id')),
      '#name' => 'foo[bar][select][container][change]',
      '#submit' => array(array($this->paymentMethodSelector, 'rebuildForm')),
      '#type' => 'submit',
      '#value' => 'Choose payment method',
    );
    $build = $method->invokeArgs($this->paymentMethodSelector, array($element, $form_state, $available_payment_methods));
    $this->assertArrayHasKey('payment_method_id', $build['container']);
    $this->assertEquals($expected_build_change, $build['container']['change']);
    $this->assertSame('container', $build['container']['#type']);
  }

}
