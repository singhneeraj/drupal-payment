<?php

/**
 * @file
 * Contains \Drupal\Tests\payment_form\Unit\Entity\Payment\PaymentFormUnitTest.
 */

namespace Drupal\Tests\payment_form\Unit\Entity\Payment;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\payment\Response\Response;
use Drupal\payment_form\Entity\Payment\PaymentForm;
use Drupal\plugin\PluginType;
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
   * The payment method plugin type.
   *
   * @var \Drupal\plugin\PluginTypeInterface
   */
  protected $paymentMethodType;

  /**
   * The plugin selector.
   *
   * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelector;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
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

    $class_resolver = $this->getMock('\Drupal\Core\DependencyInjection\ClassResolverInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $plugin_type_definition = [
      'id' => $this->randomMachineName(),
      'label' => $this->randomMachineName(),
      'provider' => $this->randomMachineName(),
    ];
    $this->paymentMethodType = new PluginType($plugin_type_definition, $this->stringTranslation, $class_resolver, $this->paymentMethodManager);

    $this->pluginSelector = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface');

    $this->pluginSelectorManager = $this->getMock('\Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface');

    $this->configFactoryConfiguration = [
      'payment_form.payment_type' => [
        'limit_allowed_plugins' => TRUE,
        'allowed_plugin_ids' => [$this->randomMachineName()],
        'plugin_selector_id' => $this->randomMachineName(),
      ],
    ];

    $this->configFactory = $this->getConfigFactoryStub($this->configFactoryConfiguration);

    $this->sut = new PaymentForm($this->entityManager, $this->stringTranslation, $this->currentUser, $this->pluginSelectorManager, $this->paymentMethodType);
    $this->sut->setConfigFactory($this->configFactory);
    $this->sut->setEntity($this->payment);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $plugin_type_manager = $this->getMock('\Drupal\plugin\PluginTypeManagerInterface');
    $plugin_type_manager->expects($this->any())
      ->method('getPluginType')
      ->with('payment_method')
      ->willReturn($this->paymentMethodType);

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = [
      ['current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser],
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
      ['plugin.manager.plugin.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager],
      ['plugin.plugin_type_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $plugin_type_manager],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
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
    $plugin_selector_build = [
      '#type' => $this->randomMachineName(),
    ];
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

    $form = [
      'langcode' => [],
    ];
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
    $form = [
      'payment_method' => [
        '#type' => $this->randomMachineName(),
      ],
    ];
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

    $form = [
      'payment_method' => [
        '#type' => $this->randomMachineName(),
      ],
    ];
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
    $plugin_a->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->currentUser)
      ->willReturn(AccessResult::allowedIf((bool) mt_rand(0, 1)));
    $plugin_b = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');
    $plugin_b->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->currentUser)
      ->willReturn(AccessResult::forbidden());

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
    $actions = $method->invokeArgs($this->sut, [$form, $form_state]);
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
    $actions = $method->invokeArgs($this->sut, [$form, $form_state]);
    $this->assertTrue($actions['submit']['#disabled']);
  }

}
