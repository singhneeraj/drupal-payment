<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Plugin\Field\FieldType\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The field item list.
   *
   * @var \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference
   */
  protected $fieldType;

  /**
   * The form builder..
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The payment queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $queue;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;
  /**
   * The field's target_id typed data property.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $targetId;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->formBuilder = $this->getMock('\Drupal\Core\Form\FormBuilderInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->queue = $this->getMock('\Drupal\payment\QueueInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->targetId = $this->getMock('\Drupal\Core\TypedData\TypedDataInterface');

    $container = new ContainerBuilder();
    $container->set('form_builder', $this->formBuilder);
    $container->set('module_handler', $this->moduleHandler);
    $container->set('payment_reference.queue', $this->queue);
    $container->set('string_translation', $this->stringTranslation);
    \Drupal::setContainer($container);

    $this->fieldType = $this->getMockBuilder('\Drupal\payment_reference\Plugin\Field\FieldType\PaymentReference')
      ->disableOriginalConstructor()
      ->setMethods(array('currencyOptions', 'get'))
      ->getMock();
    $this->fieldType->expects($this->any())
      ->method('get')
      ->with('target_id')
      ->will($this->returnValue($this->targetId));
    $this->fieldType->expects($this->any())
      ->method('getPaymentQueue')
      ->will($this->returnValue($this->queue));
  }

  /**
   * @covers ::defaultSettings
   */
  public function testDefaultSettings() {
    $settings = $this->fieldType->defaultSettings();
    $this->assertInternalType('array', $settings);
  }

  /**
   * @covers ::defaultInstanceSettings
   */
  public function testDefaultInstanceSettings() {
    $settings = $this->fieldType->defaultInstanceSettings();
    $this->assertInternalType('array', $settings);
  }

  /**
   * @covers ::schema
   */
  public function testSchema() {
    $field_storage_definition = $this->getMock('\Drupal\Core\Field\FieldStorageDefinitionInterface');

    $schema = $this->fieldType->schema($field_storage_definition);

    $this->assertInternalType('array', $schema);
    $this->arrayHasKey('columns', $schema);
    $this->assertInternalType('array', $schema['columns']);
    $this->arrayHasKey('indexes', $schema);
    $this->assertInternalType('array', $schema['indexes']);
    $this->arrayHasKey('foreign keys', $schema);
    $this->assertInternalType('array', $schema['foreign keys']);
  }

  /**
   * @covers ::settingsForm
   */
  public function testSettingsForm() {
    $form = array();
    $form_state = array();
    $has_data = TRUE;
    $this->assertSame(array(), $this->fieldType->settingsForm($form, $form_state, $has_data));
  }

  /**
   * @covers ::preSave
   */
  public function testPreSave() {
    $payment_id = mt_rand();
    $acquisition_code = $this->randomName();
    $this->targetId->expects($this->once())
      ->method('getValue')
      ->will($this->returnValue($payment_id));
    $this->queue->expects($this->once())
      ->method('claimPayment')
      ->with($payment_id)
      ->will($this->returnValue($acquisition_code));
    $this->queue->expects($this->once())
      ->method('acquirePayment')
      ->with($payment_id, $acquisition_code);
    $this->fieldType->preSave();
  }

}
