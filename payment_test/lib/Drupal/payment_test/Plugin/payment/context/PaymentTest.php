<?php

/**
 * Contains \Drupal\payment_test\Plugin\payment\context\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\payment\context;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentContext;
use Drupal\payment\Plugin\payment\context\Base;

/**
 * A testing context.
 *
 * @PaymentContext(
 *   id = "payment_test",
 *   label = @Translation("Test type")
 * )
 */
class PaymentTest extends Base {

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    return '';
  }

  /**
   * {@inheritdoc
   */
  public static function getOperations() {
    return array(
      'foo_bar' => array(
        'title' => t('FooBar'),
        'href' => '<front>',
      ),
    );
  }
}
