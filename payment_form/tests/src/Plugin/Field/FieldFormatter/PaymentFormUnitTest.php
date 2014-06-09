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
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Form Field',
      'name' => '\Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm unit test',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
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

    $this->fieldFormatter = new PaymentForm('payment_form', array(), $this->fieldDefinition, array(), $this->randomName(), $this->randomName());
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElements() {
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
    $line_items_data = array(array(
      'plugin_id' => $plugin_id,
      'plugin_configuration' => $plugin_configuration,
    ));
    $built_form = array(array(
      '#type' => 'markup',
      '#post_render_cache' => array(
        'Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm::viewElementsPostRenderCache' => array(
          array(
            'currency_code' => NULL,
            'field_definition_name' => $field_id,
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
      'currency_code' => $this->randomName(),
      'destination_url' => $this->randomName(),
      'field_definition_name' => $this->randomName(),
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
