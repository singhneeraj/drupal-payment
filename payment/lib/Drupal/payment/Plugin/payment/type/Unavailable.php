<?php

/**
 * Contains \Drupal\payment\Plugin\payment\type\Unavailable.
 */

namespace Drupal\payment\Plugin\payment\type;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An unavailable payment type.
 *
 * @PaymentType(
 *   id = "payment_unavailable",
 *   label = @Translation("Unavailable")
 * )
 */
class Unavailable extends Base {

  /**
   * {@inheritdoc}
   */
  public function resumeContext() {
    parent::resumeContext();
    throw new NotFoundHttpException();

  }

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    return t('Unavailable', array(), array(
      'langcode' => $language_code,
    ));
  }
}
