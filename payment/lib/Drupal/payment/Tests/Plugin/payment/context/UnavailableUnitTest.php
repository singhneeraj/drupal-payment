<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\payment\context\UnavailableUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\context;

use Drupal\payment\Plugin\payment\context\PaymentContextInterface ;
use Drupal\simpletest\DrupalUnitTestBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 *
 * @todo Convert this to a unit test once contexts accept payments instead of
 * payment IDs.
 */
class UnavailableUnitTest extends DrupalUnitTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\context\Unavailable unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  function setUp() {
    parent::setUp();
    $this->context = \Drupal::service('plugin.manager.payment.context')->createInstance('payment_unavailable', array());
  }

  /**
   * Tests resume().
   */
  function testResume() {
    try {
      $this->context->resume();
      $this->assertTrue(FALSE);
    }
    catch (NotFoundHttpException $exception) {
      $this->assertTrue(TRUE);
    }
  }

  /**
   * Tests paymentDescription().
   */
  function testPaymentDescription() {
    $this->assertTrue(strlen($this->context->paymentDescription()));

  }

  /**
   * Tests setPayment() and getPayment().
   */
  function testGetPayment() {
    $payment = entity_create('payment', array(
      'context' => $this->context,
    ));
    $this->assertTrue($this->context->setPayment($payment) instanceof PaymentContextInterface );
    $this->assertTrue($this->context->getPayment() === $payment);
  }
}
