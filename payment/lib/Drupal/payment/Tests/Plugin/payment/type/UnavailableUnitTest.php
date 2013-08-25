<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\payment\type\UnavailableUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\type;

use Drupal\payment\Plugin\payment\type\PaymentTypeInterface ;
use Drupal\simpletest\DrupalUnitTestBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Base.
 */
class UnavailableUnitTest extends DrupalUnitTestBase {

  /**
   * The payment type to test.
   *
   * @var \Drupal\payment\Plugin\payment\type\Unavailable
   */
  protected $type;

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
      'name' => '\Drupal\payment\Plugin\payment\type\Unavailable unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->type = $this->container->get('plugin.manager.payment.type')->createInstance('payment_unavailable', array());
  }

  /**
   * Tests resume().
   */
  protected function testResume() {
    try {
      $this->type->resumeContext();
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
    $this->assertTrue(strlen($this->type->paymentDescription()));

  }

  /**
   * Tests setPayment() and getPayment().
   */
  protected function testGetPayment() {
    $payment = entity_create('payment', array(
      'type' => $this->type,
    ));
    $this->assertTrue($this->type->setPayment($payment) instanceof PaymentTypeInterface );
    $this->assertTrue($this->type->getPayment() === $payment);
  }
}
