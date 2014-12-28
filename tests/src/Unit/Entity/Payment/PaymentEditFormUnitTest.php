<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\Payment\PaymentEditFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment {

  use Drupal\Core\Form\FormState;
  use Drupal\Core\Url;
  use Drupal\payment\Entity\Payment\PaymentEditForm;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentEditForm
   *
   * @group Payment
   */
  class PaymentEditFormUnitTest extends UnitTestCase {

    /**
     * The Currency form helper.
     *
     * @var \Drupal\currency\FormHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyFormHelper;

    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * The form under test.
     *
     * @var \Drupal\payment\Entity\Payment\PaymentEditForm
     */
    protected $form;

    /**
     * The form display.
     *
     * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formDisplay;

    /**
     * The payment.
     *
     * @var \Drupal\payment\Entity\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

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
      $this->currencyFormHelper = $this->getMock('\Drupal\currency\FormHelperInterface');

      $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

      $this->formDisplay = $this->getMock('\Drupal\Core\Entity\Display\EntityFormDisplayInterface');

      $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
      $this->stringTranslation->expects($this->any())
        ->method('translate')
        ->will($this->returnArgument(0));

      $this->form = new PaymentEditForm($this->entityManager, $this->stringTranslation, $this->currencyFormHelper);
      $this->form->setEntity($this->payment);
    }

    /**
     * @covers ::create
     */
    function testCreate() {
      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = array(
        array('currency.form_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currencyFormHelper),
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->will($this->returnValueMap($map));

      $form = PaymentEditForm::create($container);
      $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentEditForm', $form);
    }

    /**
     * @covers ::form
     */
    public function testForm() {
      $form_state = new FormState();

      $this->form->setFormDisplay($this->formDisplay, $form_state);

      $line_item_id_a = $this->randomMachineName();
      $line_item_configuration_a = array(
        'foo' => $this->randomMachineName(),
      );
      $line_item_a = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
      $line_item_a->expects($this->any())
        ->method('getPluginId')
        ->will($this->returnValue($line_item_id_a));
      $line_item_a->expects($this->any())
        ->method('getConfiguration')
        ->will($this->returnValue($line_item_configuration_a));
      $line_item_id_b = $this->randomMachineName();
      $line_item_configuration_b = array(
        'bar' => $this->randomMachineName(),
      );
      $line_item_b = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
      $line_item_b->expects($this->any())
        ->method('getPluginId')
        ->will($this->returnValue($line_item_id_b));
      $line_item_b->expects($this->any())
        ->method('getConfiguration')
        ->will($this->returnValue($line_item_configuration_b));

      $language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

      $currency_code = $this->randomMachineName();

      $this->payment->expects($this->any())
        ->method('language')
        ->will($this->returnValue($language));
      $this->payment->expects($this->any())
        ->method('getCurrencyCode')
        ->will($this->returnValue($currency_code));
      $this->payment->expects($this->any())
        ->method('getLineItems')
        ->will($this->returnValue(array($line_item_a, $line_item_b)));

      $currency_options = array(
        'baz' => $this->randomMachineName(),
        'qux' => $this->randomMachineName(),
      );
      $this->currencyFormHelper->expects($this->once())
        ->method('getCurrencyOptions')
        ->will($this->returnValue($currency_options));

      $build = $this->form->form([], $form_state);
      unset($build['#entity_builders']);
      unset($build['#process']);
      unset($build['langcode']);
      $expected_build = array(
        'payment_currency_code' => array(
          '#type' => 'select',
          '#title' => 'Currency',
          '#options' => $currency_options,
          '#default_value' => $currency_code,
          '#required' => TRUE,
        ),
        'payment_line_items' => array(
          '#type' => 'payment_line_items_input',
          '#title' => 'Line items',
          '#default_value' => array($line_item_a, $line_item_b),
          '#required' => TRUE,
          '#currency_code' => '',
        ),
      );
      $this->assertSame($expected_build, $build);
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');

      $url = new Url($this->randomMachineName());

      $this->payment->expects($this->once())
        ->method('save');
      $this->payment->expects($this->any())
        ->method('getPaymentType')
        ->will($this->returnValue($payment_type));
      $this->payment->expects($this->any())
        ->method('urlInfo')
        ->with('canonical')
        ->willReturn($url);

      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRedirectUrl')
        ->with($url);

      /** @var \Drupal\payment\Entity\Payment\PaymentEditForm|\PHPUnit_Framework_MockObject_MockObject $form */
      $form = $this->getMockBuilder('\Drupal\payment\Entity\Payment\PaymentEditForm')
        ->setConstructorArgs(array($this->entityManager, $this->stringTranslation, $this->currencyFormHelper))
        ->setMethods(array('copyFormValuesToEntity'))
        ->getMock();
      $form->setFormDisplay($this->formDisplay, $form_state);
      $form->setEntity($this->payment);

      $form->save([], $form_state);
    }

  }

}

namespace {

  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {}
  }

}
