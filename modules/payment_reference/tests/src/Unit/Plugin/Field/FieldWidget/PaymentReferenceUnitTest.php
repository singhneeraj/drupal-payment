<?php

/**
 * @file
 * Contains
 * \Drupal\Tests\payment_reference\Unit\Plugin\Field\FieldWidget\PaymentReferenceUnitTest.
 */

namespace Drupal\Tests\payment_reference\Unit\Plugin\Field\FieldWidget;

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
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The configuration the config factory returns.
   *
   * @see self::setUp
   *
   * @var array
   */
  protected $configFactoryConfiguration = [];

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
   * The payment reference factory used for testing.
   *
   * @var \Drupal\payment_reference\PaymentFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentFactory;

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

    $this->configFactoryConfiguration = array(
      'payment_reference.payment_type' => array(
        'limit_allowed_plugins' => TRUE,
        'allowed_plugin_ids' => array($this->randomMachineName()),
        'plugin_selector_id' => $this->randomMachineName(),
      ),
    );

    $this->configFactory = $this->getConfigFactoryStub($this->configFactoryConfiguration);

    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->paymentFactory = $this->getMock('\Drupal\payment_reference\PaymentFactoryInterface');

    $this->widget = new PaymentReference($this->randomMachineName(), [], $this->fieldDefinition, [], [], $this->configFactory, $this->currentUser, $this->paymentFactory);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('config.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configFactory),
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('payment_reference.payment_factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentFactory),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array(
      'field_definition' => $this->fieldDefinition,
      'settings' => [],
      'third_party_settings' => [],
    );
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $plugin = PaymentReference::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference', $plugin);
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

    $payment = $this->getMockBuilder('\Drupal\payment\ENtity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentFactory->expects($this->once())
      ->method('createPayment')
      ->with($this->fieldDefinition)
      ->will($this->returnValue($payment));

    $this->currentUser->expects($this->exactly(1))
      ->method('id')
      ->will($this->returnValue($user_id));

    $items = $this->getMockBuilder('\Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();
    $items->expects($this->atLeastOnce())
      ->method('getEntity')
      ->will($this->returnValue($entity));

    $form = [];
    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $build = $this->widget->formElement($items, 3, [], $form, $form_state);
    $expected_build = array(
      'target_id' => array(
        '#default_value' => NULL,
        '#limit_allowed_plugin_ids' => $this->configFactoryConfiguration['payment_reference.payment_type']['allowed_plugin_ids'],
        '#plugin_selector_id' => $this->configFactoryConfiguration['payment_reference.payment_type']['plugin_selector_id'],
        '#prototype_payment' => $payment,
        '#queue_category_id' => $entity_type_id . '.' . $bundle . '.' . $field_name,
        '#queue_owner_id' => (int) $user_id,
        '#required' => $required,
        '#type' => 'payment_reference',
      ),
    );
    $this->assertSame($expected_build, $build);
  }

  /**
   * @covers ::massageFormValues
   */
  public function testMassageFormValues() {
    $field_name = $this->randomMachineName();
    $payment_id = mt_rand();

    $this->fieldDefinition->expects($this->atLeastOnce())
      ->method('getName')
      ->willReturn($field_name);

    $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
    $form[$field_name]['widget']['target_id']['#value'] = $payment_id;
    $values = [];

    $expected_value = array(
      'target_id' => $payment_id,
    );
    $this->assertSame($expected_value, $this->widget->massageFormValues($values, $form, $form_state));
  }

}
