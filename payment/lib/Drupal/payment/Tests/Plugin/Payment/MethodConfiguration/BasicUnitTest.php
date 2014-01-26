<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\MethodConfiguration\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\MethodConfiguration;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic
 */
class BasicUnitTest extends UnitTestCase {

  /**
   * The payment method configuration plugin under test.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\Basic|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $plugin;

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
      'name' => '\Drupal\payment\Plugin\Payment\MethodConfiguration\Basic unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->plugin = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\MethodConfiguration\Basic')
      ->setConstructorArgs(array(array(), '', array(), $this->paymentStatusManager))
      ->setMethods(array('t'))
      ->getMock();
    $this->plugin->expects($this->any())
      ->method('t')
      ->will($this->returnArgument(0));
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $configuration = $this->plugin->defaultConfiguration();
    $this->assertInternalType('array', $configuration);
    foreach (array('brand_label', 'message_text', 'message_text_format', 'status') as $key) {
      $this->assertArrayHasKey($key, $configuration);
      $this->assertInternalType('string', $configuration[$key]);
    }
  }

  /**
   * @covers ::getStatus
   * @covers ::setStatus
   */
  public function testGetStatus() {
    $status = $this->randomName();
    $this->assertSame(spl_object_hash($this->plugin), spl_object_hash($this->plugin->setStatus($status)));
    $this->assertSame($status, $this->plugin->getStatus());
  }

  /**
   * @covers ::formElements
   */
  public function testFormElements() {
    $form = array();
    $form_state = array();
    $elements = $this->plugin->formElements($form, $form_state);
    $this->assertInternalType('array', $elements);
    foreach (array('brand_label', 'message', 'status') as $key) {
      $this->assertArrayHasKey($key, $elements);
      $this->assertInternalType('array', $elements[$key]);
    }
  }

  /**
   * @covers ::getBrandLabel
   * @covers ::setBrandLabel
   */
  public function testGetBrandLabel() {
    $label = $this->randomName();
    $this->assertSame(spl_object_hash($this->plugin), spl_object_hash($this->plugin->setBrandLabel($label)));
    $this->assertSame($label, $this->plugin->getBrandLabel());
  }
}
