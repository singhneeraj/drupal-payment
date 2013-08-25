<?php

/**
 * Contains \Drupal\payment\Plugin\payment\context\Unavailable.
 */

namespace Drupal\payment\Plugin\payment\context;


use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An unavailable context.
 *
 * @PaymentContext(
 *   id = "payment_unavailable",
 *   label = @Translation("Unavailable")
 * )
 */
class Unavailable extends Base {

  /**
   * {@inheritdoc}
   */
  public function resume() {
    parent::resume();
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
