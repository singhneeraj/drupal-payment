<?php

/**
 * Contains \Drupal\payment_test\Plugin\payment\type\PaymentTest.
 */

namespace Drupal\payment_test\Plugin\payment\type;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentType;
use Drupal\payment\Plugin\payment\type\Base;

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
