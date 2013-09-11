<?php

/**
 * Contains \Drupal\payment\Plugin\payment\status\Base.
 */

namespace Drupal\payment\Plugin\payment\status;

use Drupal\Component\Plugin\PluginBase;
use Drupal\payment\Plugin\payment\status\PaymentStatusInterface;

/**
 * A base payment status.
 */
abstract class Base extends PluginBase implements PaymentStatusInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += array(
      'created' => NULL,
      'id' => 0,
      'paymentId' => 0,
    );
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!$this->getCreated()) {
      $this->setCreated(time());
    }
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
    if (isset($this->pluginDefinition['parent_id'])) {
      $manager = \Drupal::service('plugin.manager.payment.status');
      $parent = $this->pluginDefinition['parent_id'];
      return array_unique(array_merge(array($parent), $manager->createInstance($parent)->getAncestors()));
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    $manager = \Drupal::service('plugin.manager.payment.status');
    $children = array();
    foreach ($manager->getDefinitions() as $definition) {
      if (isset($definition['parent_id']) && $definition['parent_id'] == $this->getPluginId()) {
        $children[] = $definition['id'];
      }
    }

    return $children;
  }

  /**
   * {@inheritdoc}
   */
  function getDescendants() {
    $manager = \Drupal::service('plugin.manager.payment.status');
    $children = $this->getChildren();
    $descendants = $children;
    foreach ($children as $child) {
      $descendants = array_merge($descendants, $manager->createInstance($child)->getDescendants());
    }

    return array_unique($descendants);
  }

  /**
   * {@inheritdoc}
   */
  function hasAncestor($plugin_id) {
    return in_array($plugin_id, $this->getAncestors());
  }

  /**
   * {@inheritdoc}
   */
  function isOrHasAncestor($plugin_id) {
    return $this->getPluginId() == $plugin_id|| $this->hasAncestor($plugin_id);
  }
}
