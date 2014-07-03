<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\Payment\PaymentStatusFormUnitTest.
 */

namespace Drupal\payment\Tests\Entity\Payment;

  use Drupal\Core\Language\Language;
  use Drupal\payment\Entity\Payment\PaymentStatusForm;
  use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
  use Drupal\payment\Plugin\Payment\Method\PaymentMethodUpdatePaymentStatusInterface;
  use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentStatusForm
 */
class PaymentStatusFormUnitTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The default datetime.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $defaultDateTime;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStatusForm
   */
  protected $form;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $translationManager;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\Entity\Payment\PaymentStatusForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->defaultDateTime = $this->getMockBuilder('\Drupal\Core\Datetime\DrupalDateTime')
      ->disableOriginalConstructor()
      ->getMock();

    $this->moduleHandler = $this->getmock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->paymentStatusManager = $this->getmock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->urlGenerator = $this->getmock('\Drupal\Core\Routing\UrlGeneratorInterface');

    $this->translationManager = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->form = new PaymentStatusForm($this->moduleHandler, $this->currentUser, $this->urlGenerator, $this->translationManager, $this->paymentStatusManager, $this->defaultDateTime);
    $this->form->setEntity($this->payment);
  }

  /**
   * @covers ::form
   */
  public function testFormWithDateTimeModule() {
    $settable_payment_status_ids = array($this->randomName());

    $this->paymentStatusManager->expects($this->once())
      ->method('options')
      ->with($settable_payment_status_ids);

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('datetime')
      ->will($this->returnValue(TRUE));

    $language = new Language();

    $payment_method = $this->getMock('\Drupal\payment\Tests\Entity\Payment\PaymentStatusFormUnitTestDummyPaymentMethodUpdateStatusInterface');
    $payment_method->expects($this->once())
      ->method('getSettablePaymentStatuses')
      ->with($this->currentUser)
      ->will($this->returnValue($settable_payment_status_ids));

    $this->payment->expects($this->atLeastOnce())
      ->method('getPaymentMethod')
      ->will($this->returnValue($payment_method));
    $this->payment->expects($this->any())
      ->method('language')
      ->will($this->returnValue($language));

    $form = array();
    $form_state = array();
    $form = $this->form->form($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->assertArrayHasKey('plugin_id', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('select', $form['plugin_id']['#type']);
    $this->assertArrayHasKey('created', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('datetime', $form['created']['#type']);
  }

  /**
   * @covers ::form
   */
  public function testFormWithoutDateTimeModuleWithoutPermission() {
    $this->paymentStatusManager->expects($this->once())
      ->method('options');

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('datetime')
      ->will($this->returnValue(FALSE));

    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('administer modules')
      ->will($this->returnValue(FALSE));

    $this->urlGenerator->expects($this->never())
      ->method('generateFromRoute');

    $language = new Language();

    $this->payment->expects($this->any())
      ->method('language')
      ->will($this->returnValue($language));

    $form = array();
    $form_state = array();
    $form = $this->form->form($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->assertArrayHasKey('plugin_id', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('select', $form['plugin_id']['#type']);
    $this->assertArrayHasKey('created', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('value', $form['created']['#type']);
  }

  /**
   * @covers ::form
   */
  public function testFormWithoutDateTimeModuleWithPermission() {
    $this->paymentStatusManager->expects($this->once())
      ->method('options');

    $this->moduleHandler->expects($this->once())
      ->method('moduleExists')
      ->with('datetime')
      ->will($this->returnValue(FALSE));

    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('administer modules')
      ->will($this->returnValue(TRUE));

    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->with('system.modules_list');

    $language = new Language();

    $this->payment->expects($this->any())
      ->method('language')
      ->will($this->returnValue($language));

    $form = array();
    $form_state = array();
    $form = $this->form->form($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->assertArrayHasKey('plugin_id', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('select', $form['plugin_id']['#type']);
    $this->assertArrayHasKey('created', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('value', $form['created']['#type']);
    $this->assertArrayHasKey('created_message', $form);
    $this->assertInternalType('array', $form['plugin_id']);
    $this->assertSame('markup', $form['created_message']['#type']);
  }

  /**
   * @covers ::submit
   */
  public function testSubmit() {
    $timestamp = $this->randomName();
    $plugin_id = $this->randomName();

    $payment_status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');
    $payment_status->expects($this->once())
      ->method('setCreated')
      ->with($timestamp);

    $this->defaultDateTime->expects($this->any())
      ->method('getTimestamp')
      ->will($this->returnValue($timestamp));

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id)
      ->will($this->returnValue($payment_status));

    $this->payment->expects($this->once())
      ->method('setStatus')
      ->with($payment_status);
    $this->payment->expects($this->once())
      ->method('save');

    $form = array();
    $form_state = array(
      'values' => array(
        'created' => $this->defaultDateTime,
        'plugin_id' => $plugin_id,
      ),
    );
    $this->form->submit($form, $form_state);
  }

}

/**
 * Extends two interfaces, because we can only mock one.
 */
interface PaymentStatusFormUnitTestDummyPaymentMethodUpdateStatusInterface extends PaymentMethodUpdatePaymentStatusInterface, PaymentMethodInterface {
}
