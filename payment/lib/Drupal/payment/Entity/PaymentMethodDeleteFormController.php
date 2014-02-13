<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentMethodDeleteFormController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityConfirmFormBase;

/**
 * Provides the payment method deletion form.
 */
class PaymentMethodDeleteFormController extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you really want to delete %label?', array(
      '%label' => $this->getEntity()->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'payment.payment_method.list',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'payment_method_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $payment_method = $this->getEntity();
    $payment_method->delete();
    drupal_set_message(t('%label has been deleted.', array(
      '%label' => $payment_method->label(),
    )));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }
}
