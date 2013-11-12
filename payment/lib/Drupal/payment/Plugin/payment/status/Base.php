<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Base.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Component\Plugin\PluginBase;
use Drupal\payment\Payment;

/**
 * A base payment status.
 */
abstract class Base extends PluginBase implements PaymentStatusInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!$this->getCreated()) {
      $this->setCreated(time());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'created' => NULL,
      'id' => 0,
      'paymentId' => 0,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    return $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreated($created) {
    $this->configuration['created'] = $created;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreated() {
    return $this->configuration['created'];
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentId($paymentId) {
    $this->configuration['paymentId'] = $paymentId;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentId() {
    return $this->configuration['paymentId'];
  }

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->configuration['id'] = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  function getAncestors(){
    return Payment::statusManager()->getAncestors($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    return Payment::statusManager()->getChildren($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  function getDescendants() {
    return Payment::statusManager()->getDescendants($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  function hasAncestor($plugin_id) {
    return Payment::statusManager()->hasAncestor($this->getPluginId(), $plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  function isOrHasAncestor($plugin_id) {
    return Payment::statusManager()->isOrHasAncestor($this->getPluginId(), $plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public static function getOperations($plugin_id) {
    return array();
  }
}
