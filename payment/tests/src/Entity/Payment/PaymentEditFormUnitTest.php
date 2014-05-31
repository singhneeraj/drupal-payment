<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\Payment\PaymentEditFormUnitTest.
 */

namespace Drupal\payment\Tests\Entity\Payment {

use Drupal\payment\Entity\Payment\PaymentEditForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentEditForm
 */
class PaymentEditFormUnitTest extends UnitTestCase {

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
   * The payment line item plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

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
      'name' => '\Drupal\payment\Entity\Payment\PaymentEditForm unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->formDisplay = $this->getMock('\Drupal\Core\Entity\Display\EntityFormDisplayInterface');

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentLineItemManager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->form = new PaymentEditForm($this->entityManager, $this->stringTranslation, $this->paymentLineItemManager);
    $this->form->setEntity($this->payment);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $form = PaymentEditForm::create($container);
    $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentEditForm', $form);
  }

  /**
   * @covers ::copyFormValuesToEntity
   */
  public function testCopyFormValuesToEntity() {
    $currency_code = $this->randomName();

    $this->payment->expects($this->once())
      ->method('setCurrencyCode')
      ->with($currency_code);

    $line_items_data = array(
      array(
        'plugin_id' => $this->randomName(),
        'plugin_configuration' => array(
          'foo' => $this->randomName(),
        ),
      ),
      array(
        'plugin_id' => $this->randomName(),
        'plugin_configuration' => array(
          'bar' => $this->randomName(),
        ),
      ),
    );
    $name = $this->randomName();
    $form = array(
      'payment_line_items' => array(
        '#name' => $name,
      ),
    );
    $form_state = array(
      'payment_line_item' => array(
        $name => $line_items_data,
      ),
      'values' => array(
        'payment_currency_code' => $currency_code,
      ),
    );
    $this->form->setFormDisplay($this->formDisplay, $form_state);

    $line_item_0 = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
    $line_item_1 = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');


    $map = array(
      array($line_items_data[0]['plugin_id'], $line_items_data[0]['plugin_configuration'], $line_item_0),
      array($line_items_data[1]['plugin_id'], $line_items_data[1]['plugin_configuration'], $line_item_1),
    );
    $this->paymentLineItemManager->expects($this->exactly(2))
      ->method('createInstance')
      ->will($this->returnValueMap($map));

    $this->payment->expects($this->exactly(2))
      ->method('setLineItem')
      ->with($this->isInstanceOf('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface'));

    $method = new \ReflectionMethod($this->form, 'copyFormValuesToEntity');
    $method->setAccessible(TRUE);

    $method->invokeArgs($this->form, array($this->payment, $form, &$form_state));
  }

  /**
   * @covers ::form
   */
  public function testForm() {
    $form_state = array();

    /** @var \Drupal\payment\Entity\Payment\PaymentEditForm|\PHPUnit_Framework_MockObject_MockObject $form */
    $form = $this->getMockBuilder('\Drupal\payment\Entity\Payment\PaymentEditForm')
      ->setConstructorArgs(array($this->entityManager, $this->stringTranslation, $this->paymentLineItemManager))
      ->setMethods(array('currencyOptions'))
      ->getMock();
    $form->setFormDisplay($this->formDisplay, $form_state);
    $form->setEntity($this->payment);

    $line_item_id_a = $this->randomName();
    $line_item_configuration_a = array(
      'foo' => $this->randomName(),
    );
    $line_item_a = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
    $line_item_a->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($line_item_id_a));
    $line_item_a->expects($this->any())
      ->method('getConfiguration')
      ->will($this->returnValue($line_item_configuration_a));
    $line_item_id_b = $this->randomName();
    $line_item_configuration_b = array(
      'bar' => $this->randomName(),
    );
    $line_item_b = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');
    $line_item_b->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($line_item_id_b));
    $line_item_b->expects($this->any())
      ->method('getConfiguration')
      ->will($this->returnValue($line_item_configuration_b));

    $language = $this->getMockBuilder('\Drupal\Core\Language\Language')
      ->disableOriginalConstructor()
      ->getMock();

    $currency_code = $this->randomName();

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
      'baz' => $this->randomName(),
      'qux' => $this->randomName(),
    );
    $form->expects($this->any())
      ->method('currencyOptions')
      ->will($this->returnValue($currency_options));
    $build = $form->form(array(), $form_state);
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
        '#default_value' => array(
          array(
            'plugin_id' => $line_item_id_a,
            'plugin_configuration' => $line_item_configuration_a,
          ),
          array(
            'plugin_id' => $line_item_id_b,
            'plugin_configuration' => $line_item_configuration_b,
          ),
        ),
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
    $form_state = array();

    /** @var \Drupal\payment\Entity\Payment\PaymentEditForm|\PHPUnit_Framework_MockObject_MockObject $form */
    $form = $this->getMockBuilder('\Drupal\payment\Entity\Payment\PaymentEditForm')
      ->setConstructorArgs(array($this->entityManager, $this->stringTranslation, $this->paymentLineItemManager))
      ->setMethods(array('copyFormValuesToEntity'))
      ->getMock();
    $form->setFormDisplay($this->formDisplay, $form_state);
    $form->setEntity($this->payment);

    $payment_type = $this->getMock('\Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface');

    $this->payment->expects($this->once())
      ->method('save');
    $this->payment->expects($this->any())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $form->save(array(), $form_state);
    $this->assertArrayHasKey('redirect_route', $form_state);
    /** @var \Drupal\Core\Url $url */
    $url = $form_state['redirect_route'];
    $this->assertInstanceOf('\Drupal\Core\Url', $url);
    $this->assertSame('payment.payment.view', $url->getRouteName());
  }

}

}

namespace {

  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {}
  }
  if (!function_exists('form_state_values_clean')) {
    function form_state_values_clean() {}
  }

}
