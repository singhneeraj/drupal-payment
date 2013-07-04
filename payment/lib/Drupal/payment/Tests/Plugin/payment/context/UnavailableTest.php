<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\context\UnavailableTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\context;

use Drupal\payment\Plugin\payment\context\PaymentContextInterface ;
use Drupal\simpletest\WebTestBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 *
 * @todo Convert this to a unit test once contexts accept payments instead of
 * payment IDs.
 */
class UnavailableTest extends WebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\Plugin\payment\context\Unavailable',
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
   * Tests setPaymentId() and getPaymentId().
   */
  function testGetPaymentId() {
    $id = 7;
    $this->assertTrue($this->context->setPaymentId($id) instanceof PaymentContextInterface );
    $this->assertIdentical($this->context->getPaymentId(), $id);
  }
}
