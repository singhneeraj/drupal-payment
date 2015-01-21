<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Method\PaymentMethodBaseUnitTestBase.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\Tests\UnitTestCase;

/**
 * Provides a base for tests for classes that extend
 * \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase.
 */
abstract class PaymentMethodBaseUnitTestBase extends UnitTestCase {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The token API used for testing.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $token;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The definition of the payment method plugin under test.
   *
   * @var array
   */
  protected $pluginDefinition = [];

  /**
   * The ID of the payment method plugin under test.
   *
   * @var array
   */
  protected $pluginId;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->paymentStatusManager = $this->getMock('\Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface');

    $this->token = $this->getMockBuilder('\Drupal\Core\Utility\Token')
      ->disableOriginalConstructor()
      ->getMock();

    $this->pluginDefinition = array(
      'active' => TRUE,
      'message_text' => $this->randomMachineName(),
      'message_text_format' => $this->randomMachineName(),
    );

    $this->pluginId = $this->randomMachineName();
  }

}
