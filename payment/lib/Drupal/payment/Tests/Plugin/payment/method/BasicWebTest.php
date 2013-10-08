<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\method\BasicWebTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\method;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Tests \Drupal\payment\Plugin\payment\method\Basic.
 */
class BasicWebTest extends WebTestBase {

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
      'name' => '\Drupal\payment\Plugin\payment\method\Basic web test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->methodEntity = entity_create('payment_method', array());
    $this->method = $this->container->get('plugin.manager.payment.method')->createInstance('payment_basic');
    $this->method->setPaymentMethod($this->methodEntity);
  }

  /**
   * Tests paymentFormElements().
   */
  protected function testPaymentFormElements() {
    $this->method->setMessageText('Hello [site:name]!');
    $form = array();
    $form_state = array();
    $payment = entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    ));
    $elements = $this->method->paymentFormElements($form, $form_state, $payment);
    if ($this->assertTrue(is_array($elements))) {
      $this->assertIdentical($elements['message']['#markup'], "<p>Hello Drupal!</p>\n");
    }
  }

  /**
   * Tests executePayment().
   */
  protected function testExecutePayment() {
    $plugin_id = 'payment_unknown';
    $payment = entity_create('payment', array(
      'bundle' => 'payment_unavailable',
    ))->setPaymentMethodBrand('default');
    $this->method->setStatus($plugin_id);
    $exception = FALSE;
    try {
      $this->method->executePayment($payment);
    }
    catch (NotFoundHttpException $e) {
      $exception = TRUE;

    }
    $this->assertTrue($exception);
    $this->assertEqual($payment->getStatus()->getPluginId(), $plugin_id);
  }
}
