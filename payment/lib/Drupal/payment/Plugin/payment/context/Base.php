<?php

/**
 * Contains \Drupal\payment\Plugin\payment\context\Base.
 */

namespace Drupal\payment\Plugin\payment\context;

use Drupal\Component\Plugin\PluginBase;
use Drupal\payment\Plugin\payment\context\PaymentContextInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A base context.
 */
abstract class Base extends PluginBase implements PaymentContextInterface {

  /**
   * The payment this context is of.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * {@inheritdoc}
   */
  public function getPayment() {
    return $this->payment;
  }

  /**
   * {@inheritdoc}
   */
  public function setPayment(PaymentInterface $payment) {
    $this->payment = $payment;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * Child classes are required to override this method and explicitly resume
   * the context workflow.
   */
  function resume() {
    $handler = \Drupal::moduleHandler();
    $handler->invokeAll('payment_pre_resume_context', $this->getPayment());
    // @todo Invoke Rules event.
  }

  /**
   * {@inheritdoc
   */
  public static function getOperations() {
    return array();
  }
}
