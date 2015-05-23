<?php

/**
 * @file
 * Contains \Drupal\Tests\payment_form\Unit\Entity\Payment\PaymentFormUnitTest.
 */

namespace Drupal\Tests\payment_form\Unit\Entity\Payment;

use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\payment\Response\Response;
use Drupal\payment_form\Entity\Payment\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_form\Entity\Payment\PaymentForm
 *
 * @group Payment Form Field
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The configuration the config factory returns.
   *
   * @see self::__construct
   *
   * @var array
   */
  protected $configFactoryConfiguration = [];

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment_form\Entity\Payment\PaymentForm
   */
  protected $sut;

  /**
   * The form display.
   *
   * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formDisplay;

  /**
   * A payment entity used for testing.
   *
   * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The plugin selector.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelector;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelectorManager;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->formDisplay = $this->getMock('\Drupal\Core\Entity\Display\EntityFormDisplayInterface');

    $this->payment = $this->getMock('\Drupal\payment\Entity\PaymentInterface');

    $this->paymentMethodManager = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface');

    $this->pluginSelector = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface');

    $this->pluginSelectorManager = $this->getMock('\Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->configFactoryConfiguration = array(
      'payment_form.payment_type' => array(
        'limit_allowed_plugins' => TRUE,
        'allowed_plugin_ids' => array($this->randomMachineName()),
        'plugin_selector_id' => $this->randomMachineName(),
      ),
    );

    $this->configFactory = $this->getConfigFactoryStub($this->configFactoryConfiguration);

    $this->sut = new PaymentForm($this->entityManager, $this->stringTranslation, $this->currentUser, $this->pluginSelectorManager, $this->paymentMethodManager);
    $this->sut->setConfigFactory($this->configFactory);
    $this->sut->setEntity($this->payment);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('plugin.manager.payment.method', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodManager),
      array('plugin.manager.plugin_selector.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $form = PaymentForm::create($container);
    $this->assertInstanceOf('\Drupal\payment_form\Entity\Payment\PaymentForm', $form);
  }

  /**
   * @covers ::form
   * @covers ::getPluginSelector
   */
  public function testForm() {
    $plugin_selector_build = array(
      '#type' => $this->randomMachineName(),
    );
    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('buildSelectorForm')
      ->willReturn($plugin_selector_build);

    $this->pluginSelectorManager->expects($this->once())
      ->method('createInstance')
      ->with($this->configFactoryConfiguration['payment_form.payment_type']['plugin_selector_id'])
      ->willReturn($this->pluginSelector);

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');
    $this->payment->expects($this->any())
      ->method('getPaymentType')
      ->willReturn($payment_type);
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->payment->expects($this->any())
      ->method('entityInfo')
      ->willReturn($entity_type);

    $form = array(
      'langcode' => [],
    );
    $form_state = new FormState();
    $this->sut->setFormDisplay($this->formDisplay, $form_state);
    $build = $this->sut->form($form, $form_state);
    // Build the form a second time to make sure the plugin selector is only
    // instantiated once.
    $this->sut->form($form, $form_state);
    $this->assertInternalType('array', $build);
    $this->assertArrayHasKey('line_items', $build);
    $this->assertSame($this->payment, $build['line_items']['#payment']);
    $this->assertArrayHasKey('payment_method', $build);
    $this->assertSame($plugin_selector_build, $build['payment_method']);
  }

  /**
   * @covers ::validateForm
   * @covers ::getPluginSelector
   */
  public function testValidateForm() {
    $form = array(
      'payment_method' => array(
        '#type' => $this->randomMachineName(),
      ),
    );
    $form_state = new FormState();

    $this->pluginSelectorManager->expects($this->once())
      ->method('createInstance')
      ->with($this->configFactoryConfiguration['payment_form.payment_type']['plugin_selector_id'])
      ->willReturn($this->pluginSelector);

    $this->pluginSelector->expects($this->once())
      ->method('validateSelectorForm')
      ->with($form['payment_method'], $form_state);

    $this->sut->validateForm($form, $form_state);
  }

  /**
   * @covers ::submitForm
   * @covers ::getPluginSelector
   */
  public function testSubmitForm() {
    $redirect_url = new Url($this->randomMachineName());
    $response = new Response($redirect_url);

    $result = $this->getMock('\Drupal\payment\PaymentExecutionResultInterface');
    $result->expects($this->atLeastOnce())
      ->method('getCompletionResponse')
      ->willReturn($response);

    $form = array(
      'payment_method' => array(
        '#type' => $this->randomMachineName(),
      ),
    );
    $form_state = new FormState();

    $payment_method = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $this->pluginSelectorManager->expects($this->once())
      ->method('createInstance')
      ->with($this->configFactoryConfiguration['payment_form.payment_type']['plugin_selector_id'])
      ->willReturn($this->pluginSelector);

    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('getSelectedPlugin')
      ->willReturn($payment_method);
    $this->pluginSelector->expects($this->atLeastOnce())
      ->method('submitSelectorForm')
      ->with($form['payment_method'], $form_state);

    $this->payment->expects($this->atLeastOnce())
      ->method('setPaymentMethod')
      ->with($payment_method);
    $this->payment->expects($this->atLeastOnce())
      ->method('save');
    $this->payment->expects($this->atLeastOnce())
      ->method('execute')
      ->willReturn($result);

    $this->sut->submitForm($form, $form_state);
    $this->assertSame($redirect_url, $form_state->getRedirect());
  }

  /**
   * @covers ::actions
   */
  public function testActionsWithAvailablePlugins() {
    $form = [];
    $form_state = new FormState();
    $form_state->set('plugin_selector', $this->pluginSelector);

    $plugin_id_a = $this->randomMachineName();
    $plugin_id_b = $this->randomMachineName();

    $plugin_a = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $plugin_b = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $plugin_definitions = [
      [
        'id' => $plugin_id_a,
      ],
      [
        'id' => $plugin_id_b,
      ],
    ];

    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn($plugin_definitions);
    $map = [
      [$plugin_id_a, [], $plugin_a],
      [$plugin_id_b, [], $plugin_b],
    ];
    $this->paymentMethodManager->expects($this->atLeast(count($plugin_definitions)))
      ->method('createInstance')
      ->willReturnMap($map);

    $method = new \ReflectionMethod($this->sut, 'actions');
    $method->setAccessible(TRUE);
    $actions = $method->invokeArgs($this->sut, array($form, $form_state));
    $this->assertFalse(empty($actions['submit']['#disabled']));
  }

  /**
   * @covers ::actions
   */
  public function testActionsWithoutAvailablePlugins() {
    $form = [];
    $form_state = new FormState();
    $form_state->set('plugin_selector', $this->pluginSelector);

    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn([]);

    $method = new \ReflectionMethod($this->sut, 'actions');
    $method->setAccessible(TRUE);
    $actions = $method->invokeArgs($this->sut, array($form, $form_state));
    $this->assertTrue($actions['submit']['#disabled']);
  }

}
