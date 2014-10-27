<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_form\Unit\Plugin\Field\FieldWidget\PaymentFormUnitTest.
 */

namespace Drupal\Tests\payment_form\Unit\Plugin\Field\FieldWidget;

use Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm
 *
 * @group Payment Form Field
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldDefinition;

  /**
   * The field widget under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm
   */
  protected $fieldWidget;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $this->fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $settings = array();
    $third_party_settings = array();

    $this->paymentLineItemManager = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface');

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->fieldWidget = new PaymentForm($plugin_id, $plugin_definition, $this->fieldDefinition, $settings, $third_party_settings, $this->stringTranslation, $this->paymentLineItemManager);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $configuration = array(
      'field_definition' => $field_definition,
      'settings' => array(),
      'third_party_settings' => array(),
    );
    $plugin_definition = array();
    $plugin_id = $this->randomMachineName();
    $form = PaymentForm::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm', $form);
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryWithOneLineItem() {
    $line_items_data = array(
      array(
        'plugin_id' => $this->randomMachineName(),
        'plugin_configuration' => array(),
      ),
    );
    $this->fieldWidget->setSetting('line_items', $line_items_data);
    $this->stringTranslation->expects($this->any())
      ->method('formatPlural')
      ->with(1);
    $this->fieldWidget->settingsSummary();
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryWithMultipleLineItems() {
    $line_items_data = array(
      array(
        'plugin_id' => $this->randomMachineName(),
        'plugin_configuration' => array(),
      ),
      array(
        'plugin_id' => $this->randomMachineName(),
        'plugin_configuration' => array(),
      )
    );
    $this->fieldWidget->setSetting('line_items', $line_items_data);
    $this->stringTranslation->expects($this->any())
      ->method('formatPlural')
      ->with(2);
    $this->fieldWidget->settingsSummary();
  }

  /**
   * @covers ::formElement
   */
  public function testFormElement() {
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();;
    $delta = 0;
    $element = array();
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $this->assertInternalType('array', $this->fieldWidget->formElement($items, $delta, $element, $form, $form_state));
  }

  /**
   * @covers ::formElementProcess
   */
  public function testFormElementProcess() {
    $field_storage_definition = $this->getMock('\Drupal\Core\Field\FieldStorageDefinitionInterface');

    $this->fieldDefinition->expects($this->atLeastOnce())
      ->method('getFieldStorageDefinition')
      ->will($this->returnValue($field_storage_definition));

    $iterator = new \ArrayIterator(array(
      (object) array(
      'plugin_configuration' => array(),
      'plugin_id' => $this->randomMachineName(),
    )
    ));
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->setMethods(array('getIterator'))
      ->getMock();
    $items->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($iterator));

    $element = array(
      '#array_parents' => array('line_items'),
      '#items' => $items,
    );
    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');

    $element = $this->fieldWidget->formElementProcess($element, $form_state, $form);
    $this->assertInternalType('array', $element);
    $this->arrayHasKey('array_parents', $element);
    $this->arrayHasKey('line_items', $element);
  }

}
