<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodBase;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test_uninterruptive",
 *   label = @Translation("Test method (uninterruptive)"),
 *   message_text = "Foo",
 *   message_text_format = "plain_text"
 * )
 */
class PaymentTestUninterruptive extends PaymentTestInterruptive {

  /**
   * {@inheritdoc}
   */
  public function isPaymentExecutionInterruptive() {
    return FALSE;
  }

}
