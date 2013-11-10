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
 * Tests \Drupal\payment\Plugin\payment\status\Unavailable.
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
    $this->type = \Drupal::service('plugin.manager.payment.type')->createInstance('payment_unavailable', array());
  }

  /**
   * Tests resume().
   */
  protected function testResume() {
    try {
      $this->type->resumeContext();
      $this->assert(FALSE);
    }
    catch (NotFoundHttpException $exception) {
      $this->assert(TRUE);
    }
  }

  /**
   * Tests paymentDescription().
   */
  protected function testPaymentDescription() {
    $this->assertTrue(strlen($this->type->paymentDescription()));
  }
}
