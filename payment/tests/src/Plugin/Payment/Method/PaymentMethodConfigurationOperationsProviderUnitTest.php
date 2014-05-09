<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Status\PaymentMethodConfigurationOperationsProviderUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment;

use Drupal\payment\Plugin\Payment\OperationsProviderPluginManagerTrait;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider
 */
class PaymentMethodConfigurationOperationsProviderUnitTest extends UnitTestCase {

  /**
   * The payment method configuration list builder.
   *
   * @var \Drupal\Core\Entity\EntityListBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationListBuilder;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * The provider under test
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $provider;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $request;

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
      'name' => '\Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->paymentMethodConfigurationListBuilder = $this->getMock('\Drupal\Core\Entity\EntityListBuilderInterface');

    $this->paymentMethodConfigurationStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');

    $this->provider = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider')
      ->setConstructorArgs(array($this->request, $this->stringTranslation, $this->paymentMethodConfigurationStorage, $this->paymentMethodConfigurationListBuilder))
      ->getMockForAbstractClass();
  }

  /**
   * @covers ::getOperations
   */
  public function testGetOperations() {
    $list_builder_operations = array(
      'edit' => array(
      ),
      'delete' => array(
      ),
      'enable' => array(
      ),
      'disable' => array(
      ),
      'foo' => array(
      ),
    );

    $destination = $this->randomName();

    $attributes = new ParameterBag();
    $attributes->set('_system_path', $destination);

    $this->request->attributes = $attributes;

    $plugin_id = $this->randomName();

    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();

    $this->provider->expects($this->once())
      ->method('getPaymentMethodConfiguration')
      ->with($plugin_id)
      ->will($this->returnValue($payment_method_configuration));

    $this->paymentMethodConfigurationListBuilder->expects($this->once())
      ->method('getOperations')
      ->with($payment_method_configuration)
      ->will($this->returnValue($list_builder_operations));

    $expected_operations = $list_builder_operations;
    unset($expected_operations['foo']);
    foreach ($expected_operations as $name => $expected_operation) {
      $expected_operations[$name]['title'] = NULL;
      $expected_operations[$name]['query']['destination'] = $destination;
    }
    $this->assertEquals($expected_operations, $this->provider->getOperations($plugin_id));
  }

}
