<?php

/**
 * @file
 * Contains Drupal\payment\Plugin\Core\entity\PaymentMethodDeleteFormController.
 */

namespace Drupal\payment\Plugin\Core\entity;

use Drupal\Core\Entity\EntityConfirmFormBase;

/**
 * Provides the payment method deletion form.
 */
class PaymentMethodDeleteFormController extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $payment_method = $this->getEntity();

    return t('Do you really want to delete %label?', array(
      '%label' => $payment_method->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelPath() {
    return 'admin/config/services/payment/method';
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
    $form_state['redirect'] = $this->getCancelPath();
  }
}
