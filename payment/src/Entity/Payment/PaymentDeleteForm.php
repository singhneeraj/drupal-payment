<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentDeleteForm.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Provides the payment deletion form.
 */
class PaymentDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you really want to delete payment #!payment_id?', array(
      '!payment_id' => $this->getEntity()->id(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return new Url('payment.payment.view', array(
      'payment' => $this->getEntity()->id(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->getEntity()->delete();
    drupal_set_message(t('Payment #!payment_id has been deleted.', array(
      '!id' => $this->getEntity()->id(),
    )));
    $form_state['redirect_route'] = array(
      'route_name' => '<front>',
    );
  }
}
