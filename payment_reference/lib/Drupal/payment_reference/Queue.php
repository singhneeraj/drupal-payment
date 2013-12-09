<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Queue.
 */

namespace Drupal\payment_reference;

use Drupal\Component\Utility\Random;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\payment\Plugin\Payment\Status\Manager;

/**
 * The payment reference queue manager.
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
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  protected $paymentStatusManager;

  /**
   * The random generator.
   *
   * @var \Drupal\Component\Utility\Random
   */
  protected $randomGenerator;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   * @param \Drupal\payment\Plugin\Payment\Status\Manager
   *   The payment status plugin manager.
   */
  public function __construct(Connection $database, ModuleHandlerInterface $module_handler, Manager $payment_status_manager) {
    $this->database = $database;
    $this->moduleHandler = $module_handler;
    $this->paymentStatusManager = $payment_status_manager;
    $this->randomGenerator = new Random();
  }

  /**
   * {@inheritdoc}
   */
  function save($field_instance_id, $payment_id) {
    $this->database->insert('payment_reference')
      ->fields(array(
        'field_instance_id' => $field_instance_id,
        'payment_id' => $payment_id,
      ))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function claim($payment_id) {
    $acquisition_code = $this->tryClaimOnce($payment_id);
    // If a payment cannot be claimed at the first try, wait until the prevous
    // claim has expired and try to claim the payment one more time.
    if ($acquisition_code === FALSE) {
      sleep(static::CLAIM_EXPIRATION_PERIOD);
      $acquisition_code = $this->tryClaimOnce($payment_id);
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
  protected function tryClaimOnce($payment_id) {
    $acquisition_code = $this->randomGenerator->string(255);
    $count = $this->database->update('payment_reference', array(
      'return' => Database::RETURN_AFFECTED,
    ))
      ->condition('claimed', time() - self::CLAIM_EXPIRATION_PERIOD, '<')
      ->condition('payment_id', $payment_id)
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
  public function acquire($payment_id, $acquisition_code) {
    return (bool) $this->database->delete('payment_reference', array(
      'return' => Database::RETURN_AFFECTED,
    ))
      ->condition('acquisition_code', $acquisition_code)
      ->condition('claimed', time() - self::CLAIM_EXPIRATION_PERIOD, '>=')
      ->condition('payment_id', $payment_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function release($payment_id, $acquisition_code) {
    return (bool) $this->database->update('payment_reference', array(
      'return' => Database::RETURN_AFFECTED,
    ))
      ->condition('payment_id', $payment_id)
      ->condition('acquisition_code', $acquisition_code)
      ->fields(array(
        'claimed' => 0,
      ))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  function loadFieldInstanceId($payment_id, $owner_id) {
    $query = $this->database->select('payment_reference', 'pr');
    $query->addJoin('INNER', 'payment', 'p', 'p.id = pr.payment_id');

    return $query->fields('pr', array('field_instance_id'))
      ->condition('pr.payment_id', $payment_id)
      ->condition('p.owner_id', $owner_id)
      ->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  function loadPaymentIds($field_instance_id, $owner_id) {
    $query = $this->database->select('payment_reference', 'pr');
    $query->addJoin('INNER', 'payment', 'p', 'p.id = pr.payment_id');
    $query->addJoin('INNER', 'payment_status', 'ps', 'p.last_payment_status_id = ps.id');
    $query->fields('pr', array('payment_id'))
      ->condition('pr.field_instance_id', $field_instance_id)
      ->condition('ps.plugin_id', array_merge($this->paymentStatusManager->getDescendants('payment_success'), array('payment_success')))
      ->condition('p.owner_id', $owner_id);

    $payment_ids = $query->execute()->fetchCol();
    $this->moduleHandler->alter('payment_reference_queue_payment_ids', $field_instance_id, $owner_id, $payment_ids);
    // @todo Add a Rules event.

    return $payment_ids;
  }

  /**
   * {@inheritdoc}
   */
  function deleteByPaymentId($id) {
    $this->database->delete('payment_reference')
      ->condition('payment_id', $id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  function deleteByFieldId($field_id) {
    $this->database->delete('payment_reference')
      ->condition('field_instance_id', $field_id . '.%', 'LIKE')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  function deleteByFieldInstanceId($field_instance_id) {
    $this->database->delete('payment_reference')
      ->condition('field_instance_id', $field_instance_id)
      ->execute();
  }
}
