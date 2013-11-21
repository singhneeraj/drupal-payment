<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Method\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Method;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentMethod;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\Base;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * A testing payment method.
 *
 * @PaymentMethod(
 *   id = "payment_test",
 *   label = @Translation("Test method"),
 *   operations = {
 *     "access_denied" = {
 *       "label" = @Translation("Nobody has permission to perform this operation.")
 *     },
 *     "foo_bar" = {
 *       "label" = @Translation("FooBarOperation")
 *     }
 *   }
 * )
 */
class PaymentTest extends Base {

  /**
   * {@inheritdoc}
   */
  public function currencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function brands() {
    return array(
      'default' => array(
        'currencies' => array(),
        'label' => 'Test method',
      ),
    );
  }
}
