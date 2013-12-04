<?php

/**
 * @file
 * Contains
 * \Drupal\payment\Tests\Plugin\Payment\MethodSelector\PaymentSelectUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodSelector;

use Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect.
 */
class PaymentSelectUnitTest extends UnitTestCase {

  /**
   * The current user used for testing.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The payment method manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment method selector plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect
   */
  protected $paymentMethodSelectorPlugin;

  /**
   * The ID of the payment method selector plugin under test.
   *
   * @var string
   */
  protected $paymentMethodSelectorPluginId;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    $this->currentUser = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->paymentMethodManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $configuration = array();
    $this->paymentMethodSelectorPluginId = $this->randomName();
    $plugin_definition = array();
    $this->paymentMethodSelectorPlugin = new PaymentSelect($configuration, $this->paymentMethodSelectorPluginId, $plugin_definition, $this->currentUser, $this->paymentMethodManager);
  }

  /**
   * Tests formElements().
   */
  public function testFormElements() {
    $form = array();
    $form_state = array();
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $form = $this->paymentMethodSelectorPlugin->formElements($form, $form_state, $payment);
    $this->assertInternalType('array', $form);
  }

  /**
   * Tests extractPaymentMethodFromFormElements().
   */
  public function testExtractPaymentMethodFromFormElements() {
    $element_name = $this->randomName();
    $plugin_id = $this->randomName();
    $plugin_configuration = array($this->randomName());
    $form['#name'] = $element_name;
    $form_state = array(
      $this->paymentMethodSelectorPluginId => array(
        $element_name => array(
          'payment_method_data' => array(
            'plugin_configuration' => $plugin_configuration,
            'plugin_id' => $plugin_id,
          ),
        ),
      ),
    );

    $created_instance = new \stdClass();
    $this->paymentMethodManager->expects($this->once())
      ->method('getDefinition')
      ->with($plugin_id)
      ->will($this->returnValue(array('foo')));
    $this->paymentMethodManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id)
      ->will($this->returnValue($created_instance));

    $retrieved_instance = $this->paymentMethodSelectorPlugin->getPaymentMethodFromFormElements($form, $form_state);
    $this->assertSame(spl_object_hash($created_instance), spl_object_hash($retrieved_instance));
  }
}
