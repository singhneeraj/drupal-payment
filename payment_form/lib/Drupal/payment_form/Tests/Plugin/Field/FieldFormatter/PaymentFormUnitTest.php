<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Field\FieldFormatter\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldFormatter;

use Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm.
 */
class PaymentFormUnitTest extends UnitTestCase {

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The field definition used for testing.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldDefinition;

  /**
   * The field formatter under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldFormatter;

  /**
   * The form builder used for testing.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formBuilder;

  /**
   * The payment line item manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The request used for testing.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->formBuilder = $this->getMock('\Drupal\Core\Form\FormBuilderInterface');

    $this->paymentLineItemManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');

    $this->request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $configuration = array(
      'field_definition' => $this->fieldDefinition,
      'label' => $this->randomName(),
      'settings' => array(),
      'view_mode' => $this->randomName(),
    );
    $this->fieldFormatter = new PaymentForm($configuration, 'payment_form', array(), $this->request, $this->entityManager, $this->formBuilder, $this->paymentLineItemManager);
  }

  /**
   * Tests viewElements().
   */
  public function testViewElements() {
    $payment_type = $this->getMockBuilder('\Drupal\payment_form\Plugin\Payment\Type\PaymentForm')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('setFieldInstanceConfigId');

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('setCurrencyCode')
      ->will($this->returnSelf());
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

    $form = $this->getMock('\Drupal\Core\Entity\EntityFormInterface');
    $form->expects($this->once())
      ->method('setEntity')
      ->with($payment)
      ->will($this->returnSelf());

    $storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->expects($this->once())
      ->method('create')
      ->with(array(
        'bundle' => 'payment_form',
      ))
      ->will($this->returnValue($payment));

    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with('payment')
      ->will($this->returnValue($storage));

    $this->entityManager->expects($this->once())
      ->method('getFormObject')
      ->with('payment', 'payment_form')
      ->will($this->returnValue($form));

    $plugin_id = $this->randomName();
    $plugin_configuration = array();

    $payment_line_item = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');

    $this->paymentLineItemManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id, $plugin_configuration)
      ->will($this->returnValue($payment_line_item));

    $plugin_id_property = $this->getMock('\Drupal\Core\TypedData\TypedDataInterface');
    $plugin_id_property->expects($this->once())
      ->method('getValue')
      ->will($this->returnValue($plugin_id));
    $plugin_configuration_property = $this->getMock('\Drupal\Core\TypedData\TypedDataInterface');
    $plugin_configuration_property->expects($this->once())
      ->method('getValue')
      ->will($this->returnValue($plugin_configuration));
    $map = array(
      array('plugin_id', $plugin_id_property),
      array('plugin_configuration', $plugin_configuration_property),
    );
    $item = $this->getMockBuilder('\Drupal\payment_form\Plugin\Field\FieldType\PaymentForm')
      ->disableOriginalConstructor()
      ->getMock();
    $item->expects($this->exactly(2))
      ->method('get')
      ->will($this->returnValueMap($map));

    $iterator = new \ArrayIterator(array($item));
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->setMethods(array('getIterator'))
      ->getMock();
    $items->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($iterator));

    $field_id = $this->randomName();
    $this->fieldDefinition->expects($this->once())
      ->method('getName')
      ->will($this->returnValue($field_id));

    // Create a dummy render array.
    $built_form = array($this->randomName());
    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with($form)
      ->will($this->returnValue($built_form));

    $this->assertSame($built_form, $this->fieldFormatter->viewElements($items));
  }

}
