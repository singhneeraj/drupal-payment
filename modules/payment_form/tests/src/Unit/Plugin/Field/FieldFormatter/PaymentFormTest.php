<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_form\Unit\Plugin\Field\FieldFormatter\PaymentFormTest.
 */

namespace Drupal\Tests\payment_form\Unit\Plugin\Field\FieldFormatter {

  use Drupal\Core\DependencyInjection\Container;
  use Drupal\Core\Entity\EntityFormBuilderInterface;
  use Drupal\Core\Entity\EntityInterface;
  use Drupal\Core\Entity\EntityManagerInterface;
  use Drupal\Core\Entity\EntityStorageInterface;
  use Drupal\Core\Field\FieldDefinitionInterface;
  use Drupal\Core\Field\FieldItemList;
  use Drupal\Core\Render\RendererInterface;
  use Drupal\Core\TypedData\TypedDataInterface;
  use Drupal\payment\Entity\PaymentInterface;
  use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
  use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
  use Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm;
  use Drupal\payment_form\Plugin\Field\FieldType\PaymentForm as PaymentFormFieldType;
  use Drupal\payment_form\Plugin\Payment\Type\PaymentForm as PaymentFormPaymentType;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\RequestStack;

  /**
   * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm
   *
   * @group Payment Form Field
   */
  class PaymentFormTest extends UnitTestCase {

    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * The field definition.
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
     * The entity form builder.
     *
     * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFormBuilder;

    /**
     * The payment line item manager.
     *
     * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentLineItemManager;

    /**
     * The renderer.
     *
     * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderer;

    /**
     * The request.
     *
     * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * The request stack.
     *
     * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestStack;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
      $this->entityFormBuilder = $this->getMock(EntityFormBuilderInterface::class);

      $this->entityManager = $this->getMock(EntityManagerInterface::class);

      $this->paymentLineItemManager = $this->getMock(PaymentLineItemManagerInterface::class);

      $this->fieldDefinition = $this->getMock(FieldDefinitionInterface::class);

      $this->renderer = $this->getMock(RendererInterface::class);

      $this->request = $this->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();

      $this->requestStack = $this->getMockBuilder(RequestStack::class)
        ->disableOriginalConstructor()
        ->getMock();
      $this->requestStack->expects($this->any())
        ->method('getCurrentRequest')
        ->willReturn($this->request);

      $this->fieldFormatter = new PaymentForm('payment_form', [], $this->fieldDefinition, [], $this->randomMachineName(), $this->randomMachineName(), []);
    }

    /**
     * @covers ::viewElements
     */
    public function testViewElements() {
      $entity_type_id = $this->randomMachineName();
      $bundle = $this->randomMachineName();
      $field_name = $this->randomMachineName();

      $plugin_id = $this->randomMachineName();
      $plugin_configuration = [
        $this->randomMachineName() => $this->randomMachineName(),
      ];

      $plugin_id_property = $this->getMock(TypedDataInterface::class);
      $plugin_id_property->expects($this->once())
        ->method('getValue')
        ->willReturn($plugin_id);
      $plugin_configuration_property = $this->getMock(TypedDataInterface::class);
      $plugin_configuration_property->expects($this->once())
        ->method('getValue')
        ->willReturn($plugin_configuration);
      $map = [
        ['plugin_id', $plugin_id_property],
        ['plugin_configuration', $plugin_configuration_property],
      ];
      $item = $this->getMockBuilder(PaymentFormFieldType::class)
        ->disableOriginalConstructor()
        ->getMock();
      $item->expects($this->exactly(2))
        ->method('get')
        ->willReturnMap($map);

      $entity = $this->getMock(EntityInterface::class);
      $entity->expects($this->atLeastOnce())
        ->method('bundle')
        ->willReturn($bundle);
      $entity->expects($this->atLeastOnce())
        ->method('getEntityTypeId')
        ->willReturn($entity_type_id);

      $iterator = new \ArrayIterator([$item]);
      $items = $this->getMockBuilder(FieldItemList::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntity', 'getIterator'])
        ->getMock();
      $items->expects($this->atLeastOnce())
        ->method('getEntity')
        ->willReturn($entity);
      $items->expects($this->atLeastOnce())
        ->method('getIterator')
        ->willReturn($iterator);

      $this->fieldDefinition->expects($this->once())
        ->method('getName')
        ->willReturn($field_name);

      // Create a dummy render array.
      $line_items_data = [[
        'plugin_id' => $plugin_id,
        'plugin_configuration' => $plugin_configuration,
      ]];
      $built_form = [[
        '#lazy_builder' => [
          PaymentForm::class . '::lazyBuild', [
            $bundle,
            $entity_type_id,
            $field_name,
            serialize($line_items_data),
          ],
        ],
      ]];

      $this->assertSame($built_form, $this->fieldFormatter->viewElements($items));
    }

    /**
     * @covers ::lazyBuild
     */
    public function testLazyBuild() {
      $bundle = $this->randomMachineName();
      $entity_type_id = $this->randomMachineName();
      $field_name = $this->randomMachineName();
      $destination_url = $this->randomMachineName();
      $currency_code = $this->randomMachineName();

      $this->fieldDefinition->expects($this->atLeastOnce())
        ->method('getSetting')
        ->with('currency_code')
        ->willReturn($currency_code);

      $definitions = [
        $field_name => $this->fieldDefinition,
      ];
      $this->entityManager->expects($this->atLeastOnce())
        ->method('getFieldDefinitions')
        ->with($entity_type_id, $bundle)
        ->willReturn($definitions);

      $payment_type = $this->getMockBuilder(PaymentFormPaymentType::class)
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

      $payment = $this->getMock(PaymentInterface::class);
      $payment->expects($this->once())
        ->method('setCurrencyCode')
        ->with($currency_code);
      $payment->expects($this->once())
        ->method('getPaymentType')
        ->willReturn($payment_type);

      $storage = $this->getMock(EntityStorageInterface::class);
      $storage->expects($this->once())
        ->method('create')
        ->with([
          'bundle' => 'payment_form',
        ])
        ->willReturn($payment);

      $this->entityManager->expects($this->once())
        ->method('getStorage')
        ->with('payment')
        ->willReturn($storage);

      $plugin_id = $this->randomMachineName();
      $plugin_configuration = [];

      $payment_line_item = $this->getMock(PaymentLineItemInterface::class);

      $this->paymentLineItemManager->expects($this->once())
        ->method('createInstance')
        ->with($plugin_id, $plugin_configuration)
        ->willReturn($payment_line_item);

      $form_build = [
        '#markup' => $this->randomMachineName(),
      ];

      $this->entityFormBuilder->expects($this->once())
        ->method('getForm')
        ->with($payment, 'payment_form')
        ->willReturn($form_build);

      $this->request->expects($this->atLeastOnce())
        ->method('getUri')
        ->willReturn($destination_url);

      $container = new Container();
      $container->set('entity.form_builder', $this->entityFormBuilder);
      $container->set('entity.manager', $this->entityManager);
      $container->set('plugin.manager.payment.line_item', $this->paymentLineItemManager);
      $container->set('request_stack', $this->requestStack);
      \Drupal::setContainer($container);

      $line_items_data = [[
        'plugin_id' => $plugin_id,
        'plugin_configuration' => $plugin_configuration,
      ]];

      $field_formatter = $this->fieldFormatter;
      $this->assertSame($form_build, $field_formatter::lazyBuild($bundle, $entity_type_id, $field_name, serialize($line_items_data)));
    }

  }

}

namespace {

if (!function_exists('drupal_render_cache_generate_placeholder')) {
  function drupal_render_cache_generate_placeholder() {}
}

}
