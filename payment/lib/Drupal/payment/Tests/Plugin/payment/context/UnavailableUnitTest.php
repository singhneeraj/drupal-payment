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

  /**
   * The context to test.
   *
   * @var \Drupal\payment\Plugin\payment\context\Unavailable
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\context\Unavailable unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->context = $this->container->get('plugin.manager.payment.context')->createInstance('payment_unavailable', array());
  }

  /**
   * Tests resume().
   */
  protected function testResume() {
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
  protected function testPaymentDescription() {
    $this->assertTrue(strlen($this->context->paymentDescription()));

  }

  /**
   * Tests setPayment() and getPayment().
   */
  protected function testGetPayment() {
    $payment = entity_create('payment', array(
      'context' => $this->context,
    ));
    $this->assertTrue($this->context->setPayment($payment) instanceof PaymentContextInterface );
    $this->assertTrue($this->context->getPayment() === $payment);
  }
}
