<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Action\SetStatusUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Action;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\Action\SetStatus.
 */
class SetStatusUnitTest extends UnitTestCase {

  /**
   * The action under test.
   *
   * @var \Drupal\payment\Plugin\Action\SetStatus|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $action;

  /**
   * The payment status manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Action\SetStatus unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $configuration = array();
    $plugin_definition = array();
    $plugin_id = $this->randomName();
    $this->action = $this->getMockBuilder('\Drupal\payment\Plugin\Action\SetStatus')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->paymentStatusManager))
      ->setMethods(array('t'))
      ->getMock();
  }

  /**
   * Tests defaultConfiguration().
   */
  public function testDefaultConfiguration() {
    $configuration = $this->action->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    $this->assertArrayHasKey('payment_status_plugin_id', $configuration);
  }

  /**
   * Tests buildConfigurationForm().
   */
  public function testBuildConfigurationForm() {
    $this->paymentStatusManager->expects($this->once())
      ->method('options');

    $form = array();
    $form_state = array();
    $form = $this->action->buildConfigurationForm($form, $form_state);
    $this->assertInternalType('array', $form);
    $this->assertArrayHasKey('payment_status_plugin_id', $form);
  }

  /**
   * Tests submitConfigurationForm().
   *
   * @depends testBuildConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $plugin_id = $this->randomName();
    $form = array();
    $form_state = array(
      'values' => array(
        'payment_status_plugin_id' => $plugin_id,
      ),
    );
    $this->action->submitConfigurationForm($form, $form_state);
    $configuration = $this->action->getConfiguration();
    $this->assertSame($plugin_id, $configuration['payment_status_plugin_id']);
  }

  /**
   * Tests execute().
   */
  public function testExecute() {
    $plugin_id = $this->randomName();

    $status = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface');

    $this->paymentStatusManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id)
      ->will($this->returnValue($status));

    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->once())
      ->method('setStatus')
      ->with($status);

    $this->action->setConfiguration(array(
      'payment_status_plugin_id' => $plugin_id,
    ));

    // Test execution without an argument to make sure it fails silently.
    $this->action->execute();
    $this->action->execute($payment);
  }
}
