<?php

/**
 * @file
 * Contains \Drupal\payment\Queue.
 */

namespace Drupal\payment;

use Drupal\Component\Utility\Random;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;

/**
 * The payment queue.
 */
class Queue implements QueueInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time it takes for a claim to expire.
   *
   * @var int
   *   A number of seconds.
   */
  const CLAIM_EXPIRATION_PERIOD = 1;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The database connection.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The random generator.
   *
   * @var \Drupal\Component\Utility\Random
   */
  protected $randomGenerator;

  /**
   * The unique ID of the queue (instance).
   *
   * @var string
   */
  protected $queueId;

  /**
   * Constructor.
   *
   * @param string $queue_id
   *   The unique ID of the queue (instance).
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status plugin manager.
   */
  public function __construct($queue_id, Connection $database, ModuleHandlerInterface $module_handler, PaymentStatusManagerInterface $payment_status_manager) {
    $this->database = $database;
    $this->moduleHandler = $module_handler;
    $this->paymentStatusManager = $payment_status_manager;
    $this->randomGenerator = new Random();
    $this->queueId = $queue_id;
  }

  /**
   * {@inheritdoc}
   */
  function save($category_id, $payment_id) {
    $this->database->insert('payment_queue')
      ->fields(array(
        'category_id' => $category_id,
        'payment_id' => $payment_id,
        'queue_id' => $this->queueId,
      ))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function claimPayment($payment_id) {
    $acquisition_code = $this->tryClaimPaymentOnce($payment_id);
    // If a payment cannot be claimed at the first try, wait until the prevous
    // claim has expired and try to claim the payment one more time.
    if ($acquisition_code === FALSE) {
      sleep(static::CLAIM_EXPIRATION_PERIOD);
      $acquisition_code = $this->tryClaimPaymentOnce($payment_id);
    }

    return $acquisition_code;
  }

  /**
   * Tries to claim a payment once.
   *
   * @param integer $payment_id
   *
   * @return string|false
   *   An acquisition code to acquire the payment with on success, or FALSE if
   *   the payment could not be claimed.
   */
  protected function tryClaimPaymentOnce($payment_id) {
    $acquisition_code = $this->randomGenerator->string(255);
    $count = $this->database->update('payment_queue', array(
      'return' => Database::RETURN_AFFECTED,
    ))
      ->condition('claimed', time() - self::CLAIM_EXPIRATION_PERIOD, '<')
      ->condition('payment_id', $payment_id)
      ->condition('queue_id', $this->queueId)
      ->fields(array(
        'acquisition_code' => $acquisition_code,
        'claimed' => time(),
      ))
      ->execute();

    return $count ? $acquisition_code : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function acquirePayment($payment_id, $acquisition_code) {
    return (bool) $this->database->delete('payment_queue', array(
      'return' => Database::RETURN_AFFECTED,
    ))
      ->condition('acquisition_code', $acquisition_code)
      ->condition('claimed', time() - self::CLAIM_EXPIRATION_PERIOD, '>=')
      ->condition('payment_id', $payment_id)
      ->condition('queue_id', $this->queueId)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function releaseClaim($payment_id, $acquisition_code) {
    return (bool) $this->database->update('payment_queue', array(
      'return' => Database::RETURN_AFFECTED,
    ))
      ->condition('payment_id', $payment_id)
      ->condition('acquisition_code', $acquisition_code)
      ->condition('queue_id', $this->queueId)
      ->fields(array(
        'claimed' => 0,
      ))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  function loadPaymentIds($category_id, $owner_id) {
    $query = $this->database->select('payment_queue', 'pr');
    $query->addJoin('INNER', 'payment', 'p', 'p.id = pr.payment_id');
    $query->addJoin('INNER', 'payment_status', 'ps', 'p.last_payment_status_id = ps.id');
    $query->fields('pr', array('payment_id'))
      ->condition('pr.category_id', $category_id)
      ->condition('ps.plugin_id', array_merge($this->paymentStatusManager->getDescendants('payment_success'), array('payment_success')))
      ->condition('p.owner_id', $owner_id)
      ->condition('pr.queue_id', $this->queueId);

    $payment_ids = $query->execute()->fetchCol();
    $this->moduleHandler->alter('payment_queue_payment_ids', $category_id, $owner_id, $payment_ids);
    // @todo Add a Rules event.

    return $payment_ids;
  }

  /**
   * {@inheritdoc}
   */
  function deleteByPaymentId($id) {
    $this->database->delete('payment_queue')
      ->condition('payment_id', $id)
      ->condition('queue_id', $this->queueId)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  function deleteByCategoryId($category_id) {
    $this->database->delete('payment_queue')
      ->condition('category_id', $category_id)
      ->condition('queue_id', $this->queueId)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  function deleteByCategoryIdPrefix($category_id_prefix) {
    $this->database->delete('payment_queue')
      ->condition('category_id', $category_id_prefix . '%', 'LIKE')
      ->condition('queue_id', $this->queueId)
      ->execute();
  }
}
