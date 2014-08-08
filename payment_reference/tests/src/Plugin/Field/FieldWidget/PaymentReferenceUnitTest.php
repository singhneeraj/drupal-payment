<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Plugin\Field\FieldWidget\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Field\FieldWidget;

use Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * A user account used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldDefinition;

  /**
   * The field widget plugin under test.
   *
   * @var \Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $widget;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');

    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->widget = new PaymentReference($this->randomMachineName(), array(), $this->fieldDefinition, array(), array(), $this->currentUser);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array(
      'field_definition' => $this->fieldDefinition,
      'settings' => array(),
      'third_party_settings' => array(),
    );
    $plugin_definition = array();
    $plugin_id = $this->randomMachineName();
    $plugin = PaymentReference::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_reference\Plugin\Field\FIeldWidget\PaymentReference', $plugin);
  }

  /**
   * @covers ::formElement
   */
  public function testFormElement() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();
    $field_name = $this->randomMachineName();
    $user_id = mt_rand();
    $required = TRUE;

    $entity = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->atLeastOnce())
      ->method('bundle')
      ->will($this->returnValue($bundle));
    $entity->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->will($this->returnValue($entity_type_id));

    $this->fieldDefinition->expects($this->once())
      ->method('getName')
      ->will($this->returnValue($field_name));
    $this->fieldDefinition->expects($this->once())
      ->method('isRequired')
      ->will($this->returnValue($required));
    $currency_code = 'EUR';
    $line_items_data = array(
      array(
        'plugin_configuration' => array(),
        'plugin_id' => $this->randomMachineName(),
      ),
    );
    $map = array(
      array('currency_code', $currency_code),
      array('line_items_data', $line_items_data),
    );
    $this->fieldDefinition->expects($this->exactly(2))
      ->method('getSetting')
      ->will($this->returnValueMap($map));

    $this->currentUser->expects($this->exactly(1))
      ->method('id')
      ->will($this->returnValue($user_id));

    $items = $this->getMockBuilder('\Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();
    $items->expects($this->atLeastOnce())
      ->method('getEntity')
      ->will($this->returnValue($entity));

    $form = array();
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $build = $this->widget->formElement($items, 3, array(), $form, $form_state);
    $expected_build = array(
      'payment_id' => array(
        '#bundle' => $bundle,
        '#default_value' => NULL,
        '#entity_type_id' => $entity_type_id,
        '#field_name' => $field_name,
        '#owner_id' => (int) $user_id,
        '#payment_line_items_data' => $line_items_data,
        '#payment_currency_code' => $currency_code,
        '#required' => $required,
        '#type' => 'payment_reference',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

}
