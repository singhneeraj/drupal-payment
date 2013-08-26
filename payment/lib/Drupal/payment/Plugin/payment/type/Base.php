<?php

/**
 * Contains \Drupal\payment\Plugin\payment\type\Base.
 */

namespace Drupal\payment\Plugin\payment\type;

use Drupal\Component\Plugin\PluginBase;
use Drupal\payment\Plugin\payment\type\PaymentTypeInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * A base payment type.
 */
abstract class Base extends PluginBase implements PaymentTypeInterface {

  /**
   * The payment this type is of.
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
  function resumeContext() {
    $handler = \Drupal::moduleHandler();
    $handler->invokeAll('payment_type_pre_resume_context', array($this->getPayment()));
    // @todo Invoke Rules event.
  }

  /**
   * {@inheritdoc
   */
  public static function getOperations() {
    return array();
  }
}
