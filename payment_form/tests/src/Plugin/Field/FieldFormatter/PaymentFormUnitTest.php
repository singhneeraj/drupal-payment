<?php

/**
 * @file
 * Contains
 * \Drupal\payment_form\Tests\Plugin\Field\FieldFormatter\PaymentFormUnitTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldFormatter {

  use Drupal\Core\DependencyInjection\Container;
  use Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm
 *
 * @group Payment Form Field
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
   * The entity form builder used for testing.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityFormBuilder;

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
  protected function setUp() {
    $this->entityFormBuilder = $this->getMock('\Drupal\Core\Entity\EntityFormBuilderInterface');

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->paymentLineItemManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');

    $this->request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $this->fieldFormatter = new PaymentForm('payment_form', array(), $this->fieldDefinition, array(), $this->randomName(), $this->randomName(), array());
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElements() {
    $entity_type_id = $this->randomName();
    $bundle = $this->randomName();
    $field_name = $this->randomName();

    $plugin_id = $this->randomName();
    $plugin_configuration = array(
      $this->randomName() => $this->randomName(),
    );

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

    $entity = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->atLeastOnce())
      ->method('bundle')
      ->will($this->returnValue($bundle));
    $entity->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->will($this->returnValue($entity_type_id));

    $iterator = new \ArrayIterator(array($item));
    $items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->setMethods(array('getEntity', 'getIterator'))
      ->getMock();
    $items->expects($this->atLeastOnce())
      ->method('getEntity')
      ->will($this->returnValue($entity));
    $items->expects($this->atLeastOnce())
      ->method('getIterator')
      ->will($this->returnValue($iterator));

    $this->fieldDefinition->expects($this->once())
      ->method('getName')
      ->will($this->returnValue($field_name));

    // Create a dummy render array.
    $line_items_data = array(array(
      'plugin_id' => $plugin_id,
      'plugin_configuration' => $plugin_configuration,
    ));
    $built_form = array(array(
      '#type' => 'markup',
      '#post_render_cache' => array(
        'Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm::viewElementsPostRenderCache' => array(
          array(
            'bundle' => $bundle,
            'entity_type_id' => $entity_type_id,
            'field_name' => $field_name,
            'line_items_data' => serialize($line_items_data),
          ),
        ),
      ),
      '#markup' => NULL,
    ));

    $this->assertSame($built_form, $this->fieldFormatter->viewElements($items));
  }

  /**
   * @covers ::viewElementsPostRenderCache
   */
  public function testViewElementsPostRenderCache() {
    $bundle = $this->randomName();
    $entity_type_id = $this->randomName();
    $field_name = $this->randomName();
    $destination_url = $this->randomName();
    $currency_code = $this->randomName();

    $this->fieldDefinition->expects($this->atLeastOnce())
      ->method('getSetting')
      ->with('currency_code')
      ->will($this->returnValue($currency_code));

    $definitions = array(
      $field_name => $this->fieldDefinition,
    );
    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($definitions));

    $payment_type = $this->getMockBuilder('\Drupal\payment_form\Plugin\Payment\Type\PaymentForm')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('setEntityTypeId')
      ->with($entity_type_id);
    $payment_type->expects($this->once())
      ->method('setBundle')
      ->with($bundle);
    $payment_type->expects($this->once())
      ->method('setFieldName')
      ->with($field_name);
    $payment_type->expects($this->once())
      ->method('setDestinationUrl')
      ->with($destination_url);

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('setCurrencyCode')
      ->with($currency_code);
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->will($this->returnValue($payment_type));

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

    $plugin_id = $this->randomName();
    $plugin_configuration = array();

    $payment_line_item = $this->getMock('\Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface');

    $this->paymentLineItemManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id, $plugin_configuration)
      ->will($this->returnValue($payment_line_item));

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($payment, 'payment_form');

    $this->request->expects($this->atLeastOnce())
      ->method('getUri')
      ->will($this->returnValue($destination_url));

    $container = new Container();
    $container->set('entity.form_builder', $this->entityFormBuilder);
    $container->set('entity.manager', $this->entityManager);
    $container->set('plugin.manager.payment.line_item', $this->paymentLineItemManager);
    $container->set('request', $this->request);
    \Drupal::setContainer($container);

    $line_items_data = array(array(
      'plugin_id' => $plugin_id,
      'plugin_configuration' => $plugin_configuration,
    ));

    $element = array(
      '#markup' => $this->randomName(),
    );
    $context = array(
      'bundle' => $bundle,
      'entity_type_id' => $entity_type_id,
      'field_name' => $field_name,
      'line_items_data' => serialize($line_items_data),
      'token' => $this->randomName(),
    );

    $method = new \ReflectionMethod($this->fieldFormatter, 'viewElementsPostRenderCache');
    $method->setAccessible(TRUE);
    $this->assertSame($element, $method->invoke($this->fieldFormatter, $element, $context));
  }

}

}

namespace {

if (!function_exists('drupal_render')) {
  function drupal_render() {}
}
if (!function_exists('drupal_render_cache_generate_placeholder')) {
  function drupal_render_cache_generate_placeholder() {}
}

}
