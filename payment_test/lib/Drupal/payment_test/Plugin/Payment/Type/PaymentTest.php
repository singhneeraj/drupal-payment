<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Type\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Type;

use Drupal\payment\Plugin\Payment\Type\PaymentTypeBase;

/**
 * A testing payment type.
 *
 * @PaymentType(
 *   id = "payment_test",
 *   label = @Translation("Test type")
 * )
 */
class PaymentTest extends PaymentTypeBase {

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    return 'The commander promoted Dirkjan to Major Failure.';
  }

  /**
   * {@inheritdoc
   */
  public static function getOperations($plugin_id) {
    return array(
      'foo_bar' => array(
        'title' => t('FooBar'),
        'href' => '<front>',
      ),
    );
  }
}
