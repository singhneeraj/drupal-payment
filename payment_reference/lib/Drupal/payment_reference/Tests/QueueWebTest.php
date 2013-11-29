<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\QueueWebTest.
 */

namespace Drupal\payment_reference\Tests;


use Drupal\payment\Generate;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment_reference\Queue.
 */
class QueueWebTest extends WebTestBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The payment method plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager
   */
  protected $paymentMethodManager;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  protected $paymentStatusManager;

  /**
   * The payment reference queue service under test.
   *
   * @var \Drupal\payment_reference\Queue
   */
  protected $queue;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_reference');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment Reference Field',
      'name' => '\Drupal\payment_reference\Queue web test',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp(array $modules = array()) {
    parent::setUp();
    $this->database = \Drupal::database();
    $this->paymentMethodManager = \Drupal::service('plugin.manager.payment.method');
    $this->paymentStatusManager = \Drupal::service('plugin.manager.payment.status');
    $this->queue= \Drupal::service('payment_reference.queue');
  }

  /**
   * Tests queue service.
   */
  function testQueue() {
    $field_id = 'foo.bar';
    $field_instance_id = $field_id . '.baz';
    $payment_method = Generate::createPaymentMethod(2, $this->paymentMethodManager->createInstance('payment_unavailable'));
    $payment = Generate::createPayment(2, $payment_method);
    $payment->setStatus($this->paymentStatusManager->createInstance('payment_success'));
    $payment->save();

    // Tests save().
    $this->queue->save($field_instance_id, $payment->id());
    $payment_ids = $this->queue->loadPaymentIds($field_instance_id, $payment->getOwnerId());
    $this->assertTrue(in_array($payment->id(), $payment_ids));

    // Tests loadFieldInstanceId().
    $loaded_field_instance_id = $this->queue->loadFieldInstanceId($payment->id(), $payment->getOwnerId());
    $this->assertEqual($loaded_field_instance_id, $field_instance_id);

    // Tests loadPaymentIds().
    $loaded_payment_ids = $this->queue->loadPaymentIds($field_instance_id, $payment->getOwnerId());
    $this->assertEqual($loaded_payment_ids, array($payment->id()));

    // Tests deleteByPaymentId().
    $this->queue->deleteByPaymentId($payment->id());
    $payment_ids = $this->queue->loadPaymentIds($field_instance_id, $payment->getOwnerId());
    $this->assertFalse(in_array($payment->id(), $payment_ids));

    // Tests deleteByFieldId().
    $this->queue->save($field_instance_id, $payment->id());
    $this->queue->deleteByFieldId($field_id);
    $payment_ids = $this->queue->loadPaymentIds($field_instance_id, $payment->getOwnerId());
    $this->assertFalse(in_array($payment->id(), $payment_ids));

    // Tests deleteByFieldInstanceId().
    $this->queue->save($field_instance_id, $payment->id());
    $this->queue->deleteByFieldInstanceId($field_instance_id);
    $payment_ids = $this->queue->loadPaymentIds($field_instance_id, $payment->getOwnerId());
    $this->assertFalse(in_array($payment->id(), $payment_ids));
  }
}
