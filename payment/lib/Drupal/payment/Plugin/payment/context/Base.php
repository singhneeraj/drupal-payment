<?php

/**
 * Contains \Drupal\payment\Plugin\payment\context\Base.
 */

namespace Drupal\payment\Plugin\payment\context;

use Drupal\Component\Plugin\PluginBase;
use Drupal\payment\Plugin\payment\context\ContextInterface;

/**
 * A base context.
 */
abstract class Base extends PluginBase implements PaymentContextInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += array(
      'paymentId' => 0,
    );
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
  public function setPaymentId($id) {
    $this->configuration['paymentId'] = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * Child classes are required to overrided this method and explicitly resume
   * the context workflow.
   */
  function resume() {
    $handler = \Drupal::moduleHandler();
    $handler->invokeAll('payment_pre_resume', entity_load('payment', $this->getPaymentId()));
    // @todo Invoke Rules event.
  }
}
