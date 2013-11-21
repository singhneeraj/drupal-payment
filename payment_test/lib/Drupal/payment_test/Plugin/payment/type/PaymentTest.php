<?php

/**
 * Contains \Drupal\payment_test\Plugin\Payment\Type\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\Payment\Type;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentType;
use Drupal\payment\Plugin\Payment\Type\Base;

/**
 * A testing payment type.
 *
 * @PaymentType(
 *   id = "payment_test",
 *   label = @Translation("Test type")
 * )
 */
class PaymentTest extends Base {

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
